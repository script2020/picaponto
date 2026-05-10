<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioSemana extends Model
{
    use HasFactory;

    protected $table = 'horario_semanas';

    protected $fillable = ['user_id', 'data', 'hora_inicio', 'hora_fim', 'ativo'];

    protected $casts = [
        'data'  => 'date',
        'ativo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDuracaoAttribute(): ?string
    {
        if (!$this->ativo || !$this->hora_inicio || !$this->hora_fim) {
            return null;
        }
        [$hi, $mi] = explode(':', $this->hora_inicio);
        [$hf, $mf] = explode(':', $this->hora_fim);
        $minutos = ($hf * 60 + $mf) - ($hi * 60 + $mi);
        return sprintf('%dh%02dm', intdiv($minutos, 60), $minutos % 60);
    }
}
