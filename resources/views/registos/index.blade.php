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

                    <form method="POST" action="{{ route('registos.saida', $registoAberto) }}" class="space-y-4" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="observacoes" value="Resumo do trabalho" />
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

                        <div>
                            <x-input-label value="Anexar ficheiro (opcional)" />
                            <label for="ficheiro"
                                   class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3 3m3-3l3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-700">
                                        Clique para escolher um ficheiro
                                    </span>
                                    <span id="ficheiro-nome" class="block text-xs text-gray-400 truncate">
                                        Máx. 10 MB · PDF, imagens, Office, zip…
                                    </span>
                                </span>
                            </label>
                            <input type="file" id="ficheiro" name="ficheiro" class="sr-only"
                                   onchange="document.getElementById('ficheiro-nome').textContent = this.files[0] ? this.files[0].name : 'Máx. 10 MB · PDF, imagens, Office, zip…'" />
                            <x-input-error :messages="$errors->get('ficheiro')" class="mt-1" />
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
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Histórico</h3>

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
                                    <th class="py-2 pr-4">Observações</th>
                                    <th class="py-2">Ficheiros</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($historico as $registo)
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="py-2 pr-4">{{ $registo->entrada->format('d/m/Y') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->entrada->format('H:i:s') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->saida->format('H:i:s') }}</td>
                                        <td class="py-2 pr-4">{{ $registo->duracao }}</td>
                                        <td class="py-2 pr-4 text-gray-500">{{ $registo->observacoes ?? '—' }}</td>
                                        <td class="py-2">
                                            @php $ficheiros = $registo->ficheiros ?? []; @endphp

                                            @if (count($ficheiros) > 0)
                                                <ul class="space-y-1 mb-2">
                                                    @foreach (array_reverse($ficheiros, true) as $f)
                                                        <li class="flex items-center gap-2 bg-gray-50 rounded px-2 py-1">
                                                            <a href="{{ Storage::url($f['caminho']) }}" target="_blank"
                                                               class="text-indigo-600 hover:underline truncate max-w-[12rem]">
                                                                📎 {{ $f['nome_original'] }}
                                                            </a>
                                                            <form method="POST"
                                                                  action="{{ route('registos.ficheiros.destroy', [$registo, $f['id']]) }}"
                                                                  onsubmit="return confirm('Remover este ficheiro?')" class="ml-auto">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-xs text-red-400 hover:text-red-600" title="Remover">✕</button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            {{-- Botão compacto que abre o seletor; envia automaticamente ao escolher --}}
                                            <form method="POST" action="{{ route('registos.ficheiros.store', $registo) }}"
                                                  enctype="multipart/form-data">
                                                @csrf
                                                <label class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 cursor-pointer">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                    </svg>
                                                    {{ count($ficheiros) > 0 ? 'Adicionar outro' : 'Anexar ficheiro' }}
                                                    <input type="file" name="ficheiro" required class="sr-only"
                                                           onchange="this.form.submit()" />
                                                </label>
                                            </form>
                                        </td>
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
