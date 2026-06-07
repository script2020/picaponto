<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Administração — Horas Extras por Mês
        </h2>
    </x-slot>

    @php
        function formatMinutos(int $min): string {
            $h = intdiv($min, 60);
            $m = $min % 60;
            return sprintf('%dh %02dm', $h, $m);
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtros</h3>
                <form method="GET" action="{{ route('horas.extras') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <x-input-label for="mes" value="Mês" />
                        <x-text-input id="mes" name="mes" type="month" class="mt-1"
                            value="{{ $mes }}" />
                    </div>

                    <div>
                        <x-input-label for="user_id" value="Colaborador" />
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

                    <x-primary-button type="submit">Aplicar</x-primary-button>

                    @if (request()->hasAny(['user_id', 'mes']))
                        <a href="{{ route('horas.extras') }}"
                            class="text-sm text-gray-500 underline self-end pb-2">Limpar filtros</a>
                    @endif
                </form>
            </div>

            {{-- Nota horário normal --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-5 py-3 text-sm text-blue-700">
                Horário normal: <strong>09:00–13:00</strong> e <strong>14:00–18:00</strong> (8h/dia).
                Horas extras = tempo trabalhado acima de 8h no mesmo dia.
            </div>

            {{-- Resultados --}}
            @if (empty($resultados))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Nenhum registo encontrado para o período selecionado.</p>
                </div>
            @else
                @foreach ($resultados as $resultado)
                    @php
                        $temExtras = $resultado['extras_minutos'] > 0;
                    @endphp
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        {{-- Cabeçalho do colaborador --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100
                            {{ $temExtras ? 'bg-orange-50' : 'bg-gray-50' }}">
                            <div>
                                <p class="font-semibold text-gray-800 text-base">{{ $resultado['user']->name }}</p>
                                <p class="text-xs text-gray-500">{{ $resultado['user']->email }}</p>
                            </div>
                            <div class="flex gap-6 text-sm text-right">
                                <div>
                                    <p class="text-gray-500 text-xs">Dias trabalhados</p>
                                    <p class="font-semibold text-gray-700">{{ $resultado['dias_trabalhados'] }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-xs">Total trabalhado</p>
                                    <p class="font-semibold text-gray-700">{{ formatMinutos($resultado['total_minutos']) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-xs">Horas extras</p>
                                    <p class="font-bold {{ $temExtras ? 'text-orange-600' : 'text-gray-400' }}">
                                        {{ $temExtras ? formatMinutos($resultado['extras_minutos']) : '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Detalhe por dia --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-500 uppercase border-b bg-gray-50">
                                    <tr>
                                        <th class="py-2 px-6">Data</th>
                                        <th class="py-2 px-4">Entrada / Saída</th>
                                        <th class="py-2 px-4">Total trabalhado</th>
                                        <th class="py-2 px-4">Horas normais</th>
                                        <th class="py-2 px-4">Horas extras</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($resultado['por_dia'] as $dia => $dados)
                                        @php
                                            $diaExtras  = $dados['extras_minutos'] > 0;
                                            $normaisMin = $dados['total_minutos'] - $dados['extras_minutos'];
                                        @endphp
                                        <tr class="{{ $diaExtras ? 'bg-orange-50/50' : 'hover:bg-gray-50' }}">
                                            <td class="py-3 px-6 font-medium align-top">
                                                {{ \Carbon\Carbon::parse($dia)->translatedFormat('d/m/Y') }}
                                            </td>
                                            <td class="py-3 px-4 align-top">
                                                <div class="space-y-1">
                                                    @foreach ($dados['sessoes'] as $sessao)
                                                        <div class="flex items-center gap-2 text-xs">
                                                            <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">
                                                                {{ $sessao['entrada'] }} → {{ $sessao['saida'] }}
                                                            </span>
                                                            @if ($sessao['extras_minutos'] > 0)
                                                                <span class="text-orange-500 font-medium">
                                                                    +{{ formatMinutos($sessao['extras_minutos']) }} extra
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 align-top">{{ formatMinutos($dados['total_minutos']) }}</td>
                                            <td class="py-3 px-4 align-top text-gray-500">{{ formatMinutos($normaisMin) }}</td>
                                            <td class="py-3 px-4 align-top font-semibold {{ $diaExtras ? 'text-orange-600' : 'text-gray-300' }}">
                                                {{ $diaExtras ? formatMinutos($dados['extras_minutos']) : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</x-app-layout>
