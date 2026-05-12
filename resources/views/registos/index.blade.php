<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registo de Horas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mensagens flash --}}
            @if (session('sucesso'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('sucesso') }}
                </div>
            @endif

            @if (session('erro'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('erro') }}
                </div>
            @endif

            {{-- Entrada em aberto --}}
            @if ($registoAberto)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Turno em curso</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Entrada registada às <strong>{{ $registoAberto->entrada->format('H:i:s') }}</strong>
                        de {{ $registoAberto->entrada->format('d/m/Y') }}
                    </p>

                    <form method="POST" action="{{ route('registos.saida', $registoAberto) }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="observacoes" value="Observações (opcional)" />
                            <textarea
                                id="observacoes"
                                name="observacoes"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                rows="4"
                                maxlength="1000"
                                placeholder="Ex: trabalho remoto, reunião..."
                            >{{ old('observacoes') }}</textarea>
                            <x-input-error :messages="$errors->get('observacoes')" class="mt-1" />
                        </div>

                        <x-danger-button type="submit">
                            Registar Saída
                        </x-danger-button>
                    </form>
                </div>
            @else
                {{-- Formulário de entrada --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Registar Entrada</h3>

                    <form method="POST" action="{{ route('registos.entrada') }}">
                        @csrf
                        <x-primary-button type="submit">
                            Registar Entrada
                        </x-primary-button>
                    </form>
                </div>
            @endif

            {{-- Histórico --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Histórico</h3>
                    <a href="{{ route('tarefas.index') }}"
                        class="text-sm text-blue-600 hover:underline">Ver todas as tarefas →</a>
                </div>

                @if ($historico->isEmpty())
                    <p class="text-sm text-gray-500">Ainda não existem registos concluídos.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-500 uppercase border-b">
                                <tr>
                                    <th class="py-2 pr-4">Data</th>
                                    <th class="py-2 pr-4">Entrada</th>
                                    <th class="py-2 pr-4">Saída</th>
                                    <th class="py-2 pr-4">Duração</th>
                                    <th class="py-2">Observações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($historico as $registo)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 pr-4">{{ $registo->entrada->format('d/m/Y') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->entrada->format('H:i:s') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->saida->format('H:i:s') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->duracao }}</td>
                                        <td class="py-2 text-gray-500">{{ $registo->observacoes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $historico->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
