<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use App\Models\User;
use App\Notifications\TarefaAtribuida;
use App\Notifications\TarefaComentarioAdicionado;
use App\Notifications\TarefaEstadoAlterado;
use App\Notifications\TarefaFicheiroAdicionado;
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

        if ($tarefa->atribuido_a_id && $tarefa->atribuido_a_id !== Auth::id()) {
            $tarefa->atribuidoA->notify(new TarefaAtribuida($tarefa, Auth::user()));
        }

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Tarefa criada com sucesso.');
    }

    public function update(Request $request, Tarefa $tarefa)
    {
        abort_unless($tarefa->user_id == Auth::id(), 403);

        $atribuidoAnterior = $tarefa->atribuido_a_id;

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

        $novoAtribuido = $tarefa->fresh()->atribuido_a_id;
        if ($novoAtribuido && $novoAtribuido !== Auth::id() && $novoAtribuido !== $atribuidoAnterior) {
            $tarefa->atribuidoA->notify(new TarefaAtribuida($tarefa, Auth::user()));
        }

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Tarefa atualizada.');
    }

    public function destroy(Tarefa $tarefa)
    {
        abort_unless($tarefa->user_id == Auth::id(), 403);
        abort_if($tarefa->estado === 'em_progresso', 403);

        $tarefa->delete();

        return redirect()->route('tarefas.index')->with('sucesso', 'Tarefa eliminada.');
    }

    public function adicionarComentario(Request $request, Tarefa $tarefa)
    {
        abort_unless(
            $tarefa->user_id == Auth::id() || $tarefa->atribuido_a_id == Auth::id(),
            403
        );

        $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        $tarefa->adicionarComentario($request->conteudo, Auth::user());

        $this->notificarParticipantes(
            $tarefa,
            Auth::id(),
            fn($user) => $user->notify(new TarefaComentarioAdicionado($tarefa, Auth::user(), $request->conteudo))
        );

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Comentário adicionado.');
    }

    public function adicionarRespostaComentario(Request $request, Tarefa $tarefa, string $comentarioId)
    {
        abort_unless(
            $tarefa->user_id == Auth::id() || $tarefa->atribuido_a_id == Auth::id(),
            403
        );

        $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        $tarefa->adicionarRespostaComentario($comentarioId, $request->conteudo, Auth::user());

        $this->notificarParticipantes(
            $tarefa,
            Auth::id(),
            fn($user) => $user->notify(new TarefaComentarioAdicionado($tarefa, Auth::user(), $request->conteudo))
        );

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Resposta adicionada.');
    }

    public function submeterFicheiro(Request $request, Tarefa $tarefa)
    {
        abort_unless(
            $tarefa->user_id == Auth::id() || $tarefa->atribuido_a_id == Auth::id(),
            403
        );

        $request->validate([
            'ficheiro' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png,gif,txt,csv',
        ], [
            'ficheiro.max'   => 'O ficheiro não pode exceder 10 MB.',
            'ficheiro.mimes' => 'Tipo de ficheiro não permitido.',
        ]);

        $file = $request->file('ficheiro');
        $caminho = $file->store("tarefas/{$tarefa->id}", 'public');

        $tarefa->adicionarFicheiro($file->getClientOriginalName(), $caminho, Auth::user());

        $this->notificarParticipantes(
            $tarefa,
            Auth::id(),
            fn($user) => $user->notify(new TarefaFicheiroAdicionado($tarefa, Auth::user(), $file->getClientOriginalName()))
        );

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Ficheiro submetido com sucesso.');
    }

    public function removerFicheiro(Tarefa $tarefa, string $ficheiroId)
    {
        $ficheiros = $tarefa->ficheiros ?? [];
        $alvo = collect($ficheiros)->firstWhere('id', $ficheiroId);

        abort_unless(
            $tarefa->user_id == Auth::id() || ($alvo && $alvo['user_id'] == Auth::id()),
            403
        );

        $tarefa->removerFicheiro($ficheiroId);

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Ficheiro removido.');
    }

    public function avancarEstado(Tarefa $tarefa)
    {
        abort_unless($tarefa->atribuido_a_id == Auth::id(), 403);

        $estadoAnterior = $tarefa->estado;

        $proximo = match ($tarefa->estado) {
            'pendente'     => 'em_progresso',
            'em_progresso' => 'concluida',
            default        => 'concluida',
        };

        $tarefa->update(['estado' => $proximo]);

        if ($tarefa->user_id !== Auth::id()) {
            $tarefa->user->notify(new TarefaEstadoAlterado($tarefa->fresh(), Auth::user(), $estadoAnterior));
        }

        return redirect()->route('tarefas.index', ['detalhe' => $tarefa->id])
            ->with('sucesso', 'Estado atualizado para ' . $tarefa->fresh()->estado_label . '.');
    }

    private function notificarParticipantes(Tarefa $tarefa, int $excluirId, callable $notificar): void
    {
        $ids = collect([$tarefa->user_id, $tarefa->atribuido_a_id])
            ->filter()
            ->unique()
            ->reject(fn($id) => $id === $excluirId);

        User::whereIn('id', $ids)->each($notificar);
    }
}
