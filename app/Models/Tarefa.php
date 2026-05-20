<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'ficheiros',
    ];

    protected $casts = [
        'user_id'        => 'integer',
        'atribuido_a_id' => 'integer',
        'comentarios'    => 'array',
        'ficheiros'      => 'array',
        'data'           => 'date',
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

    public function estadoLabelFrom(string $estado): string
    {
        return match ($estado) {
            'pendente'     => 'Pendente',
            'em_progresso' => 'Em Progresso',
            'concluida'    => 'Concluída',
            default        => $estado,
        };
    }
}
