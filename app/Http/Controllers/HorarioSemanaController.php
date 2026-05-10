<?php

namespace App\Http\Controllers;

use App\Models\HorarioSemana;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioSemanaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('horario.index');
    }

    public function eventos(Request $request)
    {
        $start = Carbon::parse($request->start)->startOfDay();
        $end   = Carbon::parse($request->end)->startOfDay();

        $horarios = HorarioSemana::where('user_id', Auth::id())
            ->whereBetween('data', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($h) => $h->data->toDateString());

        $events = [];

        foreach ($horarios as $dateStr => $h) {
            if (!$h->ativo) continue;

            $events[] = [
                'id'    => $dateStr,
                'title' => substr($h->hora_inicio, 0, 5) . ' – ' . substr($h->hora_fim, 0, 5),
                'start' => $dateStr . 'T' . $h->hora_inicio,
                'end'   => $dateStr . 'T' . $h->hora_fim,
                'color' => '#6366f1',
                'extendedProps' => [
                    'data'        => $dateStr,
                    'hora_inicio' => substr($h->hora_inicio, 0, 5),
                    'hora_fim'    => substr($h->hora_fim, 0, 5),
                    'ativo'       => true,
                ],
            ];
        }

        return response()->json($events);
    }

    public function adminVer(User $user)
    {
        return view('admin.horario', compact('user'));
    }

    public function adminEventos(Request $request, User $user)
    {
        $start = Carbon::parse($request->start)->startOfDay();
        $end   = Carbon::parse($request->end)->startOfDay();

        $horarios = HorarioSemana::where('user_id', $user->id)
            ->whereBetween('data', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($h) => $h->data->toDateString());

        $events = [];
        foreach ($horarios as $dateStr => $h) {
            if (!$h->ativo) continue;
            $events[] = [
                'id'    => $dateStr,
                'title' => substr($h->hora_inicio, 0, 5) . ' – ' . substr($h->hora_fim, 0, 5),
                'start' => $dateStr . 'T' . $h->hora_inicio,
                'end'   => $dateStr . 'T' . $h->hora_fim,
                'color' => '#6366f1',
                'extendedProps' => [
                    'hora_inicio' => substr($h->hora_inicio, 0, 5),
                    'hora_fim'    => substr($h->hora_fim, 0, 5),
                ],
            ];
        }

        return response()->json($events);
    }

    public function updateData(Request $request, string $data)
    {
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $data), 404);

        $date = Carbon::createFromFormat('Y-m-d', $data);
        abort_if(!$date || !$date->isValid(), 404);

        $ativo = $request->boolean('ativo');

        if ($ativo) {
            $request->validate([
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim'    => 'required|date_format:H:i|after:hora_inicio',
            ]);
        }

        HorarioSemana::updateOrCreate(
            ['user_id' => Auth::id(), 'data' => $date->toDateString()],
            [
                'ativo'       => $ativo,
                'hora_inicio' => $ativo ? $request->hora_inicio : null,
                'hora_fim'    => $ativo ? $request->hora_fim    : null,
            ]
        );

        return redirect()->route('horario.index')
            ->with('sucesso', 'Horário de ' . $date->translatedFormat('d \d\e F') . ' guardado.');
    }
}
