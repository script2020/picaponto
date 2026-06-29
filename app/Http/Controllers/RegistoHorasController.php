<?php

namespace App\Http\Controllers;

use App\Models\RegistoHoras;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class RegistoHorasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $registoAberto = RegistoHoras::where('user_id', Auth::id())
            ->whereNull('saida')
            ->latest('entrada')
            ->first();

        $historico = RegistoHoras::where('user_id', Auth::id())
            ->whereNotNull('saida')
            ->latest('entrada')
            ->paginate(10);

        return view('registos.index', compact('registoAberto', 'historico'));
    }

    public function registarEntrada(Request $request)
    {
        $jaTemEntrada = RegistoHoras::where('user_id', Auth::id())
            ->whereNull('saida')
            ->exists();

        if ($jaTemEntrada) {
            return redirect()->route('registos.index')
                ->with('erro', 'Já tem uma entrada em aberto. Registe a saída primeiro.');
        }

        RegistoHoras::create([
            'user_id' => Auth::id(),
            'entrada' => now(),
        ]);

        return redirect()->route('registos.index')
            ->with('sucesso', 'Entrada registada com sucesso às ' . now()->format('H:i') . '.');
    }

    public function registarSaida(Request $request, RegistoHoras $registo)
    {
        if ($registo->user_id != Auth::id()) {
            abort(403);
        }

        if (!$registo->estaAberto()) {
            return redirect()->route('registos.index')
                ->with('erro', 'Este registo já tem saída registada.');
        }

        $request->validate([
            'observacoes' => 'nullable|string|max:500',
            'ficheiro'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png,gif,txt,csv',
        ], [
            'ficheiro.max'   => 'O ficheiro não pode exceder 10 MB.',
            'ficheiro.mimes' => 'Tipo de ficheiro não permitido.',
        ]);

        $registo->update([
            'saida'       => now(),
            'observacoes' => $request->observacoes,
        ]);

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $caminho = $file->store("registos/{$registo->id}", 'public');
            $registo->adicionarFicheiro($file->getClientOriginalName(), $caminho, Auth::user());
        }

        return redirect()->route('registos.index')
            ->with('sucesso', 'Saída registada com sucesso às ' . now()->format('H:i') . '.');
    }

    public function submeterFicheiro(Request $request, RegistoHoras $registo)
    {
        abort_unless($registo->user_id == Auth::id(), 403);

        $request->validate([
            'ficheiro' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png,gif,txt,csv',
        ], [
            'ficheiro.max'   => 'O ficheiro não pode exceder 10 MB.',
            'ficheiro.mimes' => 'Tipo de ficheiro não permitido.',
        ]);

        $file = $request->file('ficheiro');
        $caminho = $file->store("registos/{$registo->id}", 'public');

        $registo->adicionarFicheiro($file->getClientOriginalName(), $caminho, Auth::user());

        return redirect()->route('registos.index')
            ->with('sucesso', 'Ficheiro submetido com sucesso.');
    }

    public function removerFicheiro(RegistoHoras $registo, string $ficheiroId)
    {
        abort_unless($registo->user_id == Auth::id(), 403);

        $registo->removerFicheiro($ficheiroId);

        return redirect()->route('registos.index')
            ->with('sucesso', 'Ficheiro removido.');
    }

    public function admin(Request $request)
    {
        $utilizadores = User::orderBy('name')->get();

        $query = RegistoHoras::with('user')
            ->latest('entrada');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('entrada', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('entrada', '<=', $request->data_fim);
        }

        $registos = $query->paginate(20)->withQueryString();

        return view('admin.registos', compact('registos', 'utilizadores'));
    }

    public function horasExtras(Request $request)
    {
        $utilizadores = User::orderBy('name')->get();

        $mes = $request->filled('mes') ? $request->mes : now()->format('Y-m');
        [$ano, $mesNum] = explode('-', $mes);

        $query = RegistoHoras::with('user')
            ->whereNotNull('saida')
            ->whereYear('entrada', $ano)
            ->whereMonth('entrada', $mesNum);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $registos = $query->orderBy('entrada')->get();

        $resultados = [];
        foreach ($registos->groupBy('user_id') as $registosUser) {
            $user   = $registosUser->first()->user;
            $porDia = [];

            foreach ($registosUser->groupBy(fn($r) => $r->entrada->format('Y-m-d')) as $dia => $registosDia) {
                $porDia[$dia] = [
                    'total_minutos'  => $registosDia->sum(fn($r) => $r->entrada->diffInMinutes($r->saida)),
                    'extras_minutos' => $registosDia->sum(fn($r) => $r->minutosExtras()),
                    'sessoes'        => $registosDia->map(fn($r) => [
                        'entrada'        => $r->entrada->format('H:i'),
                        'saida'          => $r->saida->format('H:i'),
                        'total_minutos'  => $r->entrada->diffInMinutes($r->saida),
                        'extras_minutos' => $r->minutosExtras(),
                    ])->values()->toArray(),
                ];
            }

            $resultados[] = [
                'user'             => $user,
                'dias_trabalhados' => count($porDia),
                'total_minutos'    => array_sum(array_column($porDia, 'total_minutos')),
                'extras_minutos'   => array_sum(array_column($porDia, 'extras_minutos')),
                'por_dia'          => $porDia,
            ];
        }

        return view('admin.horas-extras', compact('resultados', 'utilizadores', 'mes'));
    }

    public function exportarPdf(Request $request, User $user)
    {
        $query = RegistoHoras::where('user_id', $user->id)
            ->whereNotNull('saida')
            ->latest('entrada');

        if ($request->filled('data_inicio')) {
            $query->whereDate('entrada', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('entrada', '<=', $request->data_fim);
        }

        $registos = $query->get();

        $totalMinutos = $registos->sum(fn($r) => $r->entrada->diffInMinutes($r->saida));
        $horasTotal = sprintf('%dh %02dm', intdiv($totalMinutos, 60), $totalMinutos % 60);
        $media = $registos->count() > 0
            ? sprintf('%dh %02dm', intdiv($totalMinutos / $registos->count(), 60), ($totalMinutos / $registos->count()) % 60)
            : '0h 00m';

        $pdf = PDF::loadView('pdf.relatorio-horas', [
            'utilizador' => $user,
            'registos'   => $registos,
            'periodo'    => $request->only(['data_inicio', 'data_fim']),
            'horasTotal' => $horasTotal,
            'horasMedia' => $media,
        ]);

        return $pdf->download("relatorio-horas-{$user->name}-" . now()->format('Y-m-d') . '.pdf');
    }
}
