<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'atribuido_a_id',
        'data',
        'titulo',
        'descricao',
        'prioridade',
        'projeto',
        'estado',
        'comentarios',
    ];

    protected $casts = [
        'comentarios' => 'array',
        'data'        => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function atribuidoA()
    {
        return $this->belongsTo(User::class, 'atribuido_a_id');
    }

    public function adicionarComentario(string $conteudo, User $user): void
    {
        $comentarios = $this->comentarios ?? [];
        $comentarios[] = [
            'id'          => uniqid('c'),
            'conteudo'    => $conteudo,
            'user_nome'   => $user->name,
            'created_at'  => now()->toDateTimeString(),
            'replies'     => [],
        ];
        $this->update(['comentarios' => $comentarios]);
    }

    public function adicionarRespostaComentario(string $comentarioId, string $conteudo, User $user): void
    {
        $comentarios = $this->comentarios ?? [];
        $encontrado = false;

        foreach ($comentarios as &$c) {
            $cId = $c['id'] ?? null;
            if ($cId === $comentarioId || ($cId === null && 'c' . array_search($c, $comentarios, true) === $comentarioId)) {
                $c['replies'] = $c['replies'] ?? [];
                $c['replies'][] = [
                    'id'         => uniqid('r'),
                    'conteudo'   => $conteudo,
                    'user_nome'  => $user->name,
                    'created_at' => now()->toDateTimeString(),
                ];
                $encontrado = true;
                break;
            }
        }

        if ($encontrado) {
            $this->update(['comentarios' => $comentarios]);
        }
    }

    public function getPrioridadeLabelAttribute(): string
    {
        return match ($this->prioridade) {
            'alta'  => 'Alta',
            'media' => 'Média',
            'baixa' => 'Baixa',
            default => $this->prioridade,
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'pendente'     => 'Pendente',
            'em_progresso' => 'Em Progresso',
            'concluida'    => 'Concluída',
            default        => $this->estado,
        };
    }
}
