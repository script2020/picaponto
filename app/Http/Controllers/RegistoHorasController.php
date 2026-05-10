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
        ]);

        $registo->update([
            'saida'       => now(),
            'observacoes' => $request->observacoes,
        ]);

        return redirect()->route('registos.index')
            ->with('sucesso', 'Saída registada com sucesso às ' . now()->format('H:i') . '.');
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
