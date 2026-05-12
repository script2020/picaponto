<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tarefas</h2>
            <button onclick="abrirTab('nova')"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Nova Tarefa
            </button>
        </div>
    </x-slot>

    {{-- Flash --}}
    @if (session('sucesso'))
        <div id="flash-ok" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <span>{{ session('sucesso') }}</span>
                <button onclick="document.getElementById('flash-ok').remove()" class="text-green-600 hover:text-green-900 ml-4 font-bold">×</button>
            </div>
        </div>
    @endif

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Tab Bar --}}
            <div class="flex gap-1 mb-6 bg-gray-100 rounded-xl p-1 w-fit">
                @foreach ([['painel','Painel'],['todas','Todas as tarefas'],['nova','Nova Tarefa']] as [$id,$label])
                    <button id="tab-btn-{{ $id }}" onclick="abrirTab('{{ $id }}')"
                        class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition-all duration-150">
                        {{ $label }}
                    </button>
                @endforeach
                <button id="tab-btn-detalhe" onclick="abrirTab('detalhe')"
                    class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition-all duration-150 hidden">
                    Detalhe
                </button>
            </div>

            {{-- ====== PAINEL ====== --}}
            <div id="tab-painel" class="tab-pane space-y-6">

                {{-- Stats --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $cards = [
                            ['Total', $stats['total'], 'text-indigo-600', '📋'],
                            ['Em Curso', $stats['em_curso'], 'text-amber-600', '⚙️'],
                            ['Concluídas', $stats['concluidas'], 'text-green-600', '✅'],
                            ['Atribuídas a mim', $stats['minhas'], 'text-blue-600', '👤'],
                        ];
                    @endphp
                    @foreach ($cards as [$titulo, $valor, $cor, $icone])
                        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
                            <div class="text-2xl">{{ $icone }}</div>
                            <div>
                                <div class="text-2xl font-bold {{ $cor }}">{{ $valor }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $titulo }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Em aberto --}}
                @php $pendentes = $tarefas->whereIn('estado', ['pendente','em_progresso'])->take(5); @endphp
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Em aberto</h3>
                        <button onclick="abrirTab('todas')" class="text-xs text-indigo-600 hover:underline">Ver todas →</button>
                    </div>
                    @if ($pendentes->isEmpty())
                        <p class="px-6 py-6 text-sm text-gray-400">Nenhuma tarefa em aberto.</p>
                    @else
                        <ul class="divide-y divide-gray-50">
                            @foreach ($pendentes as $t)
                                <li class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition" onclick="abrirDetalhe({{ $t->id }})">
                                    @include('tarefas._linha', ['t' => $t])
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Concluídas recentemente --}}
                @php $recentes = $tarefas->where('estado','concluida')->take(5); @endphp
                @if ($recentes->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800">Concluídas recentemente</h3>
                        </div>
                        <ul class="divide-y divide-gray-50">
                            @foreach ($recentes as $t)
                                <li class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition" onclick="abrirDetalhe({{ $t->id }})">
                                    @include('tarefas._linha', ['t' => $t])
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- ====== TODAS ====== --}}
            <div id="tab-todas" class="tab-pane hidden space-y-4">

                <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3 items-center">
                    <input id="filtro-texto" type="search" placeholder="Pesquisar tarefa…"
                        oninput="filtrarTarefas()"
                        class="flex-1 min-w-48 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500" />

                    <select id="filtro-estado" onchange="filtrarTarefas()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos os estados</option>
                        <option value="pendente">Pendente</option>
                        <option value="em_progresso">Em Progresso</option>
                        <option value="concluida">Concluída</option>
                    </select>

                    <select id="filtro-prioridade" onchange="filtrarTarefas()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todas as prioridades</option>
                        <option value="alta">Alta</option>
                        <option value="media">Média</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    @if ($tarefas->isEmpty())
                        <p class="px-6 py-8 text-sm text-gray-400 text-center">Ainda não tens tarefas.</p>
                    @else
                        <ul id="lista-todas" class="divide-y divide-gray-50">
                            @foreach ($tarefas as $t)
                                <li class="tarefa-row px-6 py-4 hover:bg-gray-50 cursor-pointer transition"
                                    data-titulo="{{ strtolower($t->titulo) }}"
                                    data-estado="{{ $t->estado }}"
                                    data-prioridade="{{ $t->prioridade }}"
                                    onclick="abrirDetalhe({{ $t->id }})">
                                    @include('tarefas._linha', ['t' => $t])
                                </li>
                            @endforeach
                        </ul>
                        <p id="sem-resultados" class="hidden px-6 py-8 text-sm text-gray-400 text-center">Nenhuma tarefa corresponde aos filtros.</p>
                    @endif
                </div>
            </div>

            {{-- ====== DETALHE ====== --}}
            <div id="tab-detalhe" class="tab-pane hidden">
                <div id="detalhe-placeholder" class="bg-white rounded-xl shadow-sm px-6 py-12 text-center text-gray-400 text-sm">
                    Seleciona uma tarefa para ver o detalhe.
                </div>

                @foreach ($tarefas as $t)
                    <div id="detalhe-{{ $t->id }}" class="hidden space-y-6">

                        {{-- Header --}}
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        @php
                                            $chipsPrioridade = [
                                                'alta'  => 'bg-red-100 text-red-700',
                                                'media' => 'bg-yellow-100 text-yellow-700',
                                                'baixa' => 'bg-green-100 text-green-700',
                                            ];
                                            $chipsEstado = [
                                                'pendente'     => 'bg-gray-100 text-gray-600',
                                                'em_progresso' => 'bg-orange-100 text-orange-700',
                                                'concluida'    => 'bg-blue-100 text-blue-700',
                                            ];
                                        @endphp
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $chipsPrioridade[$t->prioridade] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $t->prioridade_label }}
                                        </span>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $chipsEstado[$t->estado] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $t->estado_label }}
                                        </span>
                                        @if ($t->projeto)
                                            <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">{{ $t->projeto }}</span>
                                        @endif
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ $t->titulo }}</h2>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Criado por <strong>{{ $t->user->name }}</strong>
                                        · {{ $t->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <button onclick="document.getElementById('tab-btn-detalhe').classList.add('hidden'); abrirTab('todas')"
                                    class="text-gray-400 hover:text-gray-700 text-xl font-bold shrink-0">×</button>
                            </div>
                        </div>

                        {{-- Metadata --}}
                        <div class="bg-white rounded-xl shadow-sm p-6 grid grid-cols-2 sm:grid-cols-4 gap-6 text-sm">
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Atribuído a</div>
                                <div class="font-medium text-gray-800">
                                    @if ($t->atribuidoA)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">
                                                {{ strtoupper(substr($t->atribuidoA->name, 0, 1)) }}
                                            </span>
                                            {{ $t->atribuidoA->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Dia</div>
                                <div class="font-medium text-gray-800">{{ $t->data ? $t->data->format('d/m/Y') : '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Prioridade</div>
                                <div class="font-medium text-gray-800">{{ $t->prioridade_label }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Estado</div>
                                <div class="font-medium text-gray-800">{{ $t->estado_label }}</div>
                            </div>
                        </div>

                        {{-- Descrição --}}
                        @if ($t->descricao)
                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Descrição</div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $t->descricao }}</p>
                            </div>
                        @endif

                        {{-- Ações --}}
                        <div class="flex flex-wrap gap-3">
                            @if ($t->estado !== 'concluida')
                                <form method="POST" action="{{ route('tarefas.avancar', $t) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                        {{ $t->estado === 'pendente' ? '▶ Iniciar' : '✓ Concluir' }}
                                    </button>
                                </form>
                            @endif

                            @if ($t->user_id === Auth::id())
                                <button onclick="toggleEditar({{ $t->id }})"
                                    class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                                    ✏️ Editar
                                </button>
                                <form method="POST" action="{{ route('tarefas.destroy', $t) }}"
                                    onsubmit="return confirm('Eliminar esta tarefa?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 bg-white border border-red-300 hover:bg-red-50 text-red-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                                        🗑 Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Progresso visual --}}
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Progresso</div>
                            @php
                                $passos = [['pendente','Pendente'],['em_progresso','Em Progresso'],['concluida','Concluída']];
                                $ordemEstado = ['pendente'=>0,'em_progresso'=>1,'concluida'=>2];
                                $ordemAtual  = $ordemEstado[$t->estado];
                            @endphp
                            <div class="flex items-center">
                                @foreach ($passos as $idx => [$val, $label])
                                    @php $feito = $ordemEstado[$val] <= $ordemAtual; @endphp
                                    <div class="flex items-center {{ $idx < count($passos) - 1 ? 'flex-1' : '' }}">
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                                {{ $feito ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                {{ $feito ? '✓' : ($idx + 1) }}
                                            </div>
                                            <span class="text-xs mt-1 whitespace-nowrap {{ $feito ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                                        </div>
                                        @if ($idx < count($passos) - 1)
                                            <div class="flex-1 h-0.5 mx-2 mb-4 {{ $ordemEstado[$passos[$idx+1][0]] <= $ordemAtual ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Formulário de edição --}}
                        @if ($t->user_id === Auth::id())
                            <div id="form-editar-{{ $t->id }}" class="hidden bg-white rounded-xl shadow-sm p-6">
                                <h3 class="font-semibold text-gray-800 mb-5">Editar Tarefa</h3>
                                <form method="POST" action="{{ route('tarefas.update', $t) }}" class="space-y-5">
                                    @csrf @method('PUT')

                                    <div>
                                        <x-input-label for="e-titulo-{{ $t->id }}" value="Título *" />
                                        <x-text-input id="e-titulo-{{ $t->id }}" name="titulo" type="text"
                                            class="mt-1 block w-full" value="{{ $t->titulo }}" required />
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <x-input-label for="e-prioridade-{{ $t->id }}" value="Prioridade" />
                                            <select id="e-prioridade-{{ $t->id }}" name="prioridade"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 font-medium"
                                                onchange="aplicarCorPrioridade(this)">
                                                <option value="baixa" @selected($t->prioridade === 'baixa')>🟢 Baixa</option>
                                                <option value="media" @selected($t->prioridade === 'media')>🟡 Média</option>
                                                <option value="alta"  @selected($t->prioridade === 'alta')>🔴 Alta</option>
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label for="e-estado-{{ $t->id }}" value="Estado" />
                                            <select id="e-estado-{{ $t->id }}" name="estado"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 font-medium"
                                                onchange="aplicarCorEstado(this)">
                                                <option value="pendente"     @selected($t->estado === 'pendente')>Pendente</option>
                                                <option value="em_progresso" @selected($t->estado === 'em_progresso')>Em Progresso</option>
                                                <option value="concluida"    @selected($t->estado === 'concluida')>Concluída</option>
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label for="e-projeto-{{ $t->id }}" value="Projeto" />
                                            <x-text-input id="e-projeto-{{ $t->id }}" name="projeto" type="text"
                                                class="mt-1 block w-full" value="{{ $t->projeto }}" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="e-data-{{ $t->id }}" value="Dia associado" />
                                            <x-text-input id="e-data-{{ $t->id }}" name="data" type="date"
                                                class="mt-1 block w-full" value="{{ $t->data ? $t->data->format('Y-m-d') : '' }}" />
                                        </div>
                                        <div>
                                            <x-input-label for="e-atrib-{{ $t->id }}" value="Atribuir a" />
                                            <select id="e-atrib-{{ $t->id }}" name="atribuido_a_id"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="{{ auth()->id() }}" @selected(($t->atribuido_a_id ?? auth()->id()) == auth()->id())>
                                                    Eu próprio ({{ auth()->user()->name }})
                                                </option>
                                                @foreach ($utilizadores as $u)
                                                    @if ($u->id !== auth()->id())
                                                        <option value="{{ $u->id }}" @selected($t->atribuido_a_id == $u->id)>{{ $u->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="e-desc-{{ $t->id }}" value="Descrição" />
                                        <textarea id="e-desc-{{ $t->id }}" name="descricao" rows="3" maxlength="2000"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ $t->descricao }}</textarea>
                                    </div>

                                    <div class="flex gap-3">
                                        <x-primary-button type="submit">Guardar</x-primary-button>
                                        <button type="button" onclick="toggleEditar({{ $t->id }})"
                                            class="text-sm text-gray-500 hover:underline">Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        {{-- Comentários --}}
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Comentários</div>
                            @php $comentarios = $t->comentarios ?? []; @endphp
                            @if (count($comentarios) === 0)
                                <p class="text-sm text-gray-400 mb-4">Sem comentários ainda.</p>
                            @else
                                <ul class="space-y-5 mb-5">
                                    @foreach (array_reverse($comentarios, true) as $idx => $c)
                                        @php
                                            $cId = $c['id'] ?? 'c' . $idx;
                                        @endphp
                                        <li>
                                            {{-- Main comment --}}
                                            <div class="flex gap-3">
                                                <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                                    {{ strtoupper(substr($c['user_nome'], 0, 1)) }}
                                                </div>
                                                <div class="flex-1 bg-gray-50 rounded-lg px-4 py-3">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-xs font-semibold text-gray-700">{{ $c['user_nome'] }}</span>
                                                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($c['created_at'])->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700">{{ $c['conteudo'] }}</p>
                                                </div>
                                            </div>

                                            {{-- Reply form --}}
                                            <form method="POST" action="{{ route('tarefas.resposta', [$t, $cId]) }}" class="mt-2 ml-10 flex gap-2">
                                                @csrf
                                                <input type="text" name="conteudo" required maxlength="1000" placeholder="Escreve uma resposta…"
                                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500" />
                                                <button type="submit"
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                                    Responder
                                                </button>
                                            </form>

                                            {{-- Replies --}}
                                            @php $replies = $c['replies'] ?? []; @endphp
                                            @if (!empty($replies))
                                                <ul class="mt-3 ml-10 space-y-3 border-l-2 border-gray-200 pl-4">
                                                    @foreach (array_reverse($replies) as $r)
                                                        <li class="flex gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                                                {{ strtoupper(substr($r['user_nome'], 0, 1)) }}
                                                            </div>
                                                            <div class="flex-1 bg-gray-50 rounded-lg px-3 py-2">
                                                                <div class="flex items-center justify-between mb-0.5">
                                                                    <span class="text-xs font-semibold text-gray-700">{{ $r['user_nome'] }}</span>
                                                                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($r['created_at'])->diffForHumans() }}</span>
                                                                </div>
                                                                <p class="text-xs text-gray-700">{{ $r['conteudo'] }}</p>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            {{-- New top-level comment form --}}
                            <form method="POST" action="{{ route('tarefas.comentarios', $t) }}" class="flex gap-3 border-t border-gray-200 pt-4">
                                @csrf
                                <input type="text" name="conteudo" required maxlength="1000" placeholder="Escreve um comentário…"
                                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                    Enviar
                                </button>
                            </form>
                        </div>

                    </div>{{-- /detalhe-N --}}
                @endforeach
            </div>

            {{-- ====== NOVA TAREFA ====== --}}
            <div id="tab-nova" class="tab-pane hidden">
                <div class="bg-white rounded-xl shadow-sm p-8 max-w-3xl">
                    <h3 class="font-semibold text-gray-800 text-lg mb-6">Nova Tarefa</h3>

                    <form method="POST" action="{{ route('tarefas.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="titulo" value="Título *" />
                            <x-text-input id="titulo" name="titulo" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('titulo') }}" required maxlength="255" autofocus
                                placeholder="Descreve a tarefa…" />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <x-input-label for="prioridade" value="Prioridade" />
                                <select id="prioridade" name="prioridade"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 font-medium"
                                    onchange="aplicarCorPrioridade(this)">
                                    <option value="baixa" @selected(old('prioridade') === 'baixa')>🟢 Baixa</option>
                                    <option value="media" @selected(old('prioridade', 'media') === 'media')>🟡 Média</option>
                                    <option value="alta"  @selected(old('prioridade') === 'alta')>🔴 Alta</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="estado" value="Estado" />
                                <select id="estado" name="estado"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 font-medium"
                                    onchange="aplicarCorEstado(this)">
                                    <option value="pendente"     @selected(old('estado', 'pendente') === 'pendente')>Pendente</option>
                                    <option value="em_progresso" @selected(old('estado') === 'em_progresso')>Em Progresso</option>
                                    <option value="concluida"    @selected(old('estado') === 'concluida')>Concluída</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="projeto" value="Projeto / Departamento" />
                                <x-text-input id="projeto" name="projeto" type="text"
                                    class="mt-1 block w-full" value="{{ old('projeto') }}" maxlength="255" placeholder="Ex: TI, Marketing…" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="data" value="Dia associado (opcional)" />
                                <x-text-input id="data" name="data" type="date"
                                    class="mt-1 block w-full" value="{{ old('data') }}" />
                            </div>
                            <div>
                                <x-input-label for="atribuido_a_id" value="Atribuir a" />
                                <select id="atribuido_a_id" name="atribuido_a_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="{{ auth()->id() }}" @selected(old('atribuido_a_id', auth()->id()) == auth()->id())>
                                        Eu próprio ({{ auth()->user()->name }})
                                    </option>
                                    @foreach ($utilizadores as $u)
                                        @if ($u->id !== auth()->id())
                                            <option value="{{ $u->id }}" @selected(old('atribuido_a_id') == $u->id)>{{ $u->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="descricao" value="Descrição (opcional)" />
                            <textarea id="descricao" name="descricao" rows="4" maxlength="2000"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Detalhes adicionais…">{{ old('descricao') }}</textarea>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button type="submit">Criar Tarefa</x-primary-button>
                            <button type="button" onclick="abrirTab('painel')" class="text-sm text-gray-500 hover:underline">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        const TABS = ['painel', 'todas', 'detalhe', 'nova'];

        function abrirTab(nome) {
            TABS.forEach(t => {
                const pane = document.getElementById('tab-' + t);
                const btn  = document.getElementById('tab-btn-' + t);
                if (!pane) return;
                const ativo = t === nome;
                pane.classList.toggle('hidden', !ativo);
                if (btn) {
                    if (ativo) {
                        btn.classList.add('bg-white', 'shadow-sm', 'text-indigo-700', 'font-semibold');
                        btn.classList.remove('text-gray-500');
                    } else {
                        btn.classList.remove('bg-white', 'shadow-sm', 'text-indigo-700', 'font-semibold');
                        btn.classList.add('text-gray-500');
                    }
                }
            });
        }

        function abrirDetalhe(id) {
            document.querySelectorAll('[id^="detalhe-"]').forEach(el => {
                if (el.id !== 'detalhe-placeholder') el.classList.add('hidden');
            });
            const alvo = document.getElementById('detalhe-' + id);
            if (!alvo) return;
            alvo.classList.remove('hidden');
            document.getElementById('detalhe-placeholder').classList.add('hidden');
            const btn = document.getElementById('tab-btn-detalhe');
            btn.classList.remove('hidden');
            abrirTab('detalhe');
        }

        function toggleEditar(id) {
            const form = document.getElementById('form-editar-' + id);
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                const sp = document.getElementById('e-prioridade-' + id);
                const se = document.getElementById('e-estado-' + id);
                if (sp) aplicarCorPrioridade(sp);
                if (se) aplicarCorEstado(se);
            }
        }

        function filtrarTarefas() {
            const texto      = document.getElementById('filtro-texto').value.toLowerCase();
            const estado     = document.getElementById('filtro-estado').value;
            const prioridade = document.getElementById('filtro-prioridade').value;
            const rows       = document.querySelectorAll('.tarefa-row');
            let visiveis = 0;
            rows.forEach(row => {
                const ok = (!texto      || row.dataset.titulo.includes(texto))
                        && (!estado     || row.dataset.estado === estado)
                        && (!prioridade || row.dataset.prioridade === prioridade);
                row.classList.toggle('hidden', !ok);
                if (ok) visiveis++;
            });
            document.getElementById('sem-resultados').classList.toggle('hidden', visiveis > 0);
        }

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
            const sp = document.getElementById('prioridade');
            const se = document.getElementById('estado');
            if (sp) aplicarCorPrioridade(sp);
            if (se) aplicarCorEstado(se);

            const params    = new URLSearchParams(window.location.search);
            const detalheId = params.get('detalhe') || '{{ $abrir }}';

            if (detalheId && detalheId !== 'null' && detalheId !== '') {
                abrirDetalhe(parseInt(detalheId));
            } else if ({{ $errors->any() ? 'true' : 'false' }}) {
                abrirTab('nova');
            } else {
                abrirTab('painel');
            }
        });
    </script>
</x-app-layout>
