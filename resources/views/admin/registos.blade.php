<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Administração — Registos de Horas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtros</h3>
                <form method="GET" action="{{ route('registos.admin') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <x-input-label for="user_id" value="Utilizador" />
                        <select id="user_id" name="user_id"
                            class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach ($utilizadores as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="data_inicio" value="De" />
                        <x-text-input id="data_inicio" name="data_inicio" type="date" class="mt-1"
                            value="{{ request('data_inicio') }}" />
                    </div>

                    <div>
                        <x-input-label for="data_fim" value="Até" />
                        <x-text-input id="data_fim" name="data_fim" type="date" class="mt-1"
                            value="{{ request('data_fim') }}" />
                    </div>

                    <x-primary-button type="submit">Aplicar</x-primary-button>

                    @if (request()->hasAny(['user_id', 'data_inicio', 'data_fim']))
                        <a href="{{ route('registos.admin') }}"
                            class="text-sm text-gray-500 underline self-end pb-2">Limpar filtros</a>
                    @endif
                </form>
            </div>

            {{-- Horas Extras --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Horas Extras</h3>
                        <p class="text-sm text-gray-500 mt-1">Horário normal: 09:00–13:00 e 14:00–18:00 (8h/dia).</p>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        @if (request('user_id'))
                            @php $mesAtual = now()->format('Y-m'); @endphp
                            <a href="{{ route('horas.extras', ['user_id' => request('user_id'), 'mes' => $mesAtual]) }}"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm font-medium">
                                Ver horas extras — {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                            </a>
                        @endif
                        <a href="{{ route('horas.extras') }}"
                            class="px-4 py-2 bg-orange-50 text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-100 text-sm font-medium">
                            Relatório completo de horas extras
                        </a>
                    </div>
                </div>
            </div>

            {{-- Exportar PDF --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Exportar Relatório</h3>
                @if (request('user_id'))
                    @php $userSelecionado = $utilizadores->find(request('user_id')); @endphp
                    <form method="GET" action="{{ route('registos.user.pdf', ['user' => request('user_id')]) }}"
                        class="flex flex-wrap gap-4 items-end">
                        <input type="hidden" name="data_inicio" value="{{ request('data_inicio') }}">
                        <input type="hidden" name="data_fim" value="{{ request('data_fim') }}">
                        <div class="text-sm text-gray-600">
                            <p class="mb-3">
                                Utilizador: <strong>{{ $userSelecionado?->name ?? '—' }}</strong>
                                @if (request('data_inicio') || request('data_fim'))
                                    <br>Período:
                                    <strong>
                                        {{ request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : '—' }}
                                        até
                                        {{ request('data_fim') ? \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : '—' }}
                                    </strong>
                                @endif
                            </p>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                            📥 Descarregar PDF
                        </button>
                        @if ($userSelecionado)
                            <a href="{{ route('horario.admin.ver', $userSelecionado) }}"
                               class="px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 text-sm font-medium">
                                Ver horário
                            </a>
                        @endif
                    </form>
                @else
                    <p class="text-sm text-gray-500">Selecione um utilizador nos filtros para exportar o relatório ou ver o horário.</p>
                @endif
            </div>

            {{-- Tabela --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($registos->isEmpty())
                    <p class="text-sm text-gray-500">Nenhum registo encontrado.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-500 uppercase border-b">
                                <tr>
                                    <th class="py-2 pr-4">Utilizador</th>
                                    <th class="py-2 pr-4">Data</th>
                                    <th class="py-2 pr-4">Entrada</th>
                                    <th class="py-2 pr-4">Saída</th>
                                    <th class="py-2 pr-4">Duração</th>
                                    <th class="py-2 pr-4">Observações</th>
                                    <th class="py-2">Ficheiros</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($registos as $registo)
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="py-2 pr-4 font-medium">{{ $registo->user->name }}</td>
                                        <td class="py-2 pr-4">{{ $registo->entrada->format('d/m/Y') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->entrada->format('H:i:s') }}</td>
                                        <td class="py-2 pr-4">
                                            {{ $registo->saida ? $registo->saida->format('H:i:s') : '—' }}
                                        </td>
                                        <td class="py-2 pr-4">
                                            @if ($registo->duracao)
                                                {{ $registo->duracao }}
                                            @else
                                                <span class="text-green-600 font-medium">Em curso</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4 text-gray-500">{{ $registo->observacoes ?? '—' }}</td>
                                        <td class="py-2">
                                            @php $ficheiros = $registo->ficheiros ?? []; @endphp
                                            @if (count($ficheiros) === 0)
                                                <span class="text-gray-400">—</span>
                                            @else
                                                <ul class="space-y-1">
                                                    @foreach (array_reverse($ficheiros, true) as $f)
                                                        <li>
                                                            <a href="{{ Storage::url($f['caminho']) }}" target="_blank"
                                                               class="inline-flex items-center gap-1 text-indigo-600 hover:underline truncate max-w-[14rem]">
                                                                📎 {{ $f['nome_original'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $registos->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
