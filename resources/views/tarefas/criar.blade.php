<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nova Tarefa
            </h2>
            <a href="{{ route('tarefas.index') }}" class="text-sm text-gray-500 hover:underline">
                ← Voltar às tarefas
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg px-10 py-10">

                <form method="POST" action="{{ route('tarefas.store') }}" class="space-y-8">
                    @csrf

                    <div>
                        <x-input-label for="titulo" value="Título *" />
                        <x-text-input id="titulo" name="titulo" type="text"
                            class="mt-2 block w-full"
                            value="{{ old('titulo') }}" required maxlength="255" autofocus
                            placeholder="Descreve a tarefa…" />
                        <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="prioridade" value="Prioridade" />
                            <select id="prioridade" name="prioridade"
                                class="mt-2 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 font-medium"
                                onchange="aplicarCorPrioridade(this)">
                                <option value="baixa" @selected(old('prioridade') === 'baixa')>🟢 Baixa</option>
                                <option value="media" @selected(old('prioridade', 'media') === 'media')>🟡 Média</option>
                                <option value="alta"  @selected(old('prioridade') === 'alta')>🔴 Alta</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="estado" value="Estado" />
                            <select id="estado" name="estado"
                                class="mt-2 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 font-medium"
                                onchange="aplicarCorEstado(this)">
                                <option value="pendente"     @selected(old('estado', 'pendente') === 'pendente')>Pendente</option>
                                <option value="em_progresso" @selected(old('estado') === 'em_progresso')>Em Progresso</option>
                                <option value="concluida"    @selected(old('estado') === 'concluida')>Concluída</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="projeto" value="Projeto / Departamento" />
                            <x-text-input id="projeto" name="projeto" type="text"
                                class="mt-2 block w-full"
                                value="{{ old('projeto') }}" maxlength="255" placeholder="Ex: TI, Marketing…" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="data" value="Dia associado (opcional)" />
                            <x-text-input id="data" name="data" type="date"
                                class="mt-2 block w-full"
                                value="{{ old('data') }}" />
                        </div>

                        <div>
                            <x-input-label for="atribuido_a_id" value="Atribuir a" />
                            <select id="atribuido_a_id" name="atribuido_a_id"
                                class="mt-2 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="{{ auth()->id() }}" @selected(old('atribuido_a_id', auth()->id()) == auth()->id())>
                                    Eu próprio ({{ auth()->user()->name }})
                                </option>
                                @foreach ($utilizadores as $u)
                                    @if ($u->id !== auth()->id())
                                        <option value="{{ $u->id }}" @selected(old('atribuido_a_id') == $u->id)>
                                            {{ $u->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="descricao" value="Descrição (opcional)" />
                        <textarea id="descricao" name="descricao" rows="4" maxlength="2000"
                            class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Detalhes adicionais sobre a tarefa…">{{ old('descricao') }}</textarea>
                        <x-input-error :messages="$errors->get('descricao')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button type="submit">Criar Tarefa</x-primary-button>
                        <a href="{{ route('tarefas.index') }}" class="text-sm text-gray-500 hover:underline">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        const coresPrioridade = {
            alta:  { bg: '#fee2e2', color: '#991b1b', border: '#fca5a5' },
            media: { bg: '#fef9c3', color: '#854d0e', border: '#fde047' },
            baixa: { bg: '#dcfce7', color: '#166534', border: '#86efac' },
        };

        const coresEstado = {
            pendente:     { bg: '#f3f4f6', color: '#374151', border: '#d1d5db' },
            em_progresso: { bg: '#fff7ed', color: '#9a3412', border: '#fdba74' },
            concluida:    { bg: '#eff6ff', color: '#1e40af', border: '#93c5fd' },
        };

        function aplicarCorPrioridade(el) {
            const c = coresPrioridade[el.value];
            if (!c) return;
            el.style.backgroundColor = c.bg;
            el.style.color           = c.color;
            el.style.borderColor     = c.border;
        }

        function aplicarCorEstado(el) {
            const c = coresEstado[el.value];
            if (!c) return;
            el.style.backgroundColor = c.bg;
            el.style.color           = c.color;
            el.style.borderColor     = c.border;
        }

        document.addEventListener('DOMContentLoaded', () => {
            aplicarCorPrioridade(document.getElementById('prioridade'));
            aplicarCorEstado(document.getElementById('estado'));
        });
    </script>
</x-app-layout>
