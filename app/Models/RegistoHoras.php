<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RegistoHoras extends Model
{
    use HasFactory;

    protected $table = 'registos_horas';

    protected $fillable = ['user_id', 'entrada', 'saida', 'observacoes', 'ficheiros'];

    protected $casts = [
        'entrada'   => 'datetime',
        'saida'     => 'datetime',
        'ficheiros' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adicionarFicheiro(string $nomeOriginal, string $caminho, User $user): void
    {
        $ficheiros = $this->ficheiros ?? [];
        $ficheiros[] = [
            'id'            => uniqid('f'),
            'nome_original' => $nomeOriginal,
            'caminho'       => $caminho,
            'user_nome'     => $user->name,
            'user_id'       => $user->id,
            'created_at'    => now()->toDateTimeString(),
        ];
        $this->update(['ficheiros' => $ficheiros]);
    }

    public function removerFicheiro(string $ficheiroId): void
    {
        $ficheiros = $this->ficheiros ?? [];
        $alvo = collect($ficheiros)->firstWhere('id', $ficheiroId);

        if ($alvo) {
            Storage::disk('public')->delete($alvo['caminho']);
        }

        $this->update([
            'ficheiros' => array_values(array_filter($ficheiros, fn($f) => ($f['id'] ?? null) !== $ficheiroId)),
        ]);
    }

    public function getDuracaoAttribute(): ?string
    {
        if (!$this->saida) {
            return null;
        }
        $totalSegundos = $this->entrada->diffInSeconds($this->saida);
        $horas = intdiv($totalSegundos, 3600);
        $minutos = intdiv($totalSegundos % 3600, 60);
        $segundos = $totalSegundos % 60;

        return sprintf('%dh %02dm %02ds', $horas, $minutos, $segundos);
    }

    public function estaAberto(): bool
    {
        return is_null($this->saida);
    }

    /**
     * Calculates overtime minutes for this session by finding time worked
     * outside the normal schedule (09:00–13:00 and 14:00–18:00).
     */
    public function minutosExtras(): int
    {
        if (!$this->saida) {
            return 0;
        }

        $dia = $this->entrada->format('Y-m-d');

        // Tempo antes das 09:00 não é contabilizado
        $entrada = $this->entrada->max(Carbon::parse("$dia 09:00"));

        $janelas = [
            [Carbon::parse("$dia 09:00"), Carbon::parse("$dia 13:00")],
            [Carbon::parse("$dia 14:00"), Carbon::parse("$dia 18:00")],
        ];

        $totalMin  = $entrada->diffInMinutes($this->saida);
        $normalMin = 0;

        foreach ($janelas as [$inicio, $fim]) {
            $overlapInicio = $entrada->max($inicio);
            $overlapFim    = $this->saida->min($fim);
            if ($overlapFim > $overlapInicio) {
                $normalMin += $overlapInicio->diffInMinutes($overlapFim);
            }
        }

        return max(0, $totalMin - $normalMin);
    }
}
