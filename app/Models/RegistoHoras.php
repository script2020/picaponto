<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistoHoras extends Model
{
    use HasFactory;

    protected $table = 'registos_horas';

    protected $fillable = ['user_id', 'entrada', 'saida', 'observacoes'];

    protected $casts = [
        'entrada' => 'datetime',
        'saida'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
