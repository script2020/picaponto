<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $tarefas = Tarefa::with(['user', 'atribuidoA'])
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('atribuido_a_id', Auth::id());
            })
            ->latest()
            ->get();

        $stats = [
            'total'     => $tarefas->count(),
            'em_curso'  => $tarefas->where('estado', 'em_progresso')->count(),
            'concluidas'=> $tarefas->where('estado', 'concluida')->count(),
            'minhas'    => $tarefas->where('atribuido_a_id', Auth::id())->count(),
        ];

        $utilizadores = User::orderBy('name')->get();
        $abrir = $request->query('detalhe');

        return view('tarefas.index', compact('tarefas', 'stats', 'utilizadores', 'abrir'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'         => 'required|string|max:255',
            'descricao'      => 'nullable|string|max:2000',
            'prioridade'     => 'required|in:baixa,media,alta',
            'projeto'        => 'nullable|string|max:255',
            'estado'         => 'required|in:pendente,em_progresso,concluida',
            'data'           => 'nullable|date',
            'atribuido_a_id' => 'nullable|exists:users,id',
        ]);

        $validated['user_id'] = Auth::id();

        $tarefa = Tarefa::create($validated);

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Tarefa criada com sucesso.');
    }

    public function update(Request $request, Tarefa $tarefa)
    {
        abort_unless($tarefa->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'titulo'         => 'required|string|max:255',
            'descricao'      => 'nullable|string|max:2000',
            'prioridade'     => 'required|in:baixa,media,alta',
            'projeto'        => 'nullable|string|max:255',
            'estado'         => 'required|in:pendente,em_progresso,concluida',
            'data'           => 'nullable|date',
            'atribuido_a_id' => 'nullable|exists:users,id',
        ]);

        $tarefa->update($validated);

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Tarefa atualizada.');
    }

    public function destroy(Tarefa $tarefa)
    {
        abort_unless($tarefa->user_id === Auth::id(), 403);

        $tarefa->delete();

        return redirect()->route('tarefas.index')->with('sucesso', 'Tarefa eliminada.');
    }

    public function adicionarComentario(Request $request, Tarefa $tarefa)
    {
        abort_unless(
            $tarefa->user_id === Auth::id() || $tarefa->atribuido_a_id === Auth::id(),
            403
        );

        $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        $tarefa->adicionarComentario($request->conteudo, Auth::user());

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Comentário adicionado.');
    }

    public function adicionarRespostaComentario(Request $request, Tarefa $tarefa, string $comentarioId)
    {
        abort_unless(
            $tarefa->user_id === Auth::id() || $tarefa->atribuido_a_id === Auth::id(),
            403
        );

        $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        $tarefa->adicionarRespostaComentario($comentarioId, $request->conteudo, Auth::user());

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Resposta adicionada.');
    }

    public function avancarEstado(Tarefa $tarefa)
    {
        abort_unless(
            $tarefa->user_id === Auth::id() || $tarefa->atribuido_a_id === Auth::id(),
            403
        );

        $proximo = match ($tarefa->estado) {
            'pendente'     => 'em_progresso',
            'em_progresso' => 'concluida',
            default        => 'concluida',
        };

        $tarefa->update(['estado' => $proximo]);

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Estado atualizado para ' . $tarefa->fresh()->estado_label . '.');
    }
}
