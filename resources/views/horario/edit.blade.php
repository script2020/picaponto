<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Horário Semanal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('sucesso'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('sucesso') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Define o teu horário de trabalho habitual. Os dias sem visto são considerados folga.
                </p>

                <form method="POST" action="{{ route('horario.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="divide-y divide-gray-100">
                        @foreach (\App\Models\HorarioSemana::$dias as $numero => $nome)
                            @php
                                $h = $horarios[$numero] ?? null;
                                $ativo = $h?->ativo ?? ($numero <= 5);
                            @endphp

                            <div class="py-4" x-data="{ ativo: {{ $ativo ? 'true' : 'false' }} }">
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 w-36 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            name="dias[{{ $numero }}][ativo]"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            x-model="ativo"
                                        >
                                        <span class="text-sm font-medium text-gray-700">{{ $nome }}</span>
                                    </label>

                                    <div class="flex items-center gap-3 flex-1" x-show="ativo">
                                        <div class="flex items-center gap-2">
                                            <x-input-label value="Entrada" class="text-xs whitespace-nowrap" />
                                            <input
                                                type="time"
                                                name="dias[{{ $numero }}][hora_inicio]"
                                                value="{{ $h?->hora_inicio ? substr($h->hora_inicio, 0, 5) : '09:00' }}"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            >
                                        </div>

                                        <span class="text-gray-400">—</span>

                                        <div class="flex items-center gap-2">
                                            <x-input-label value="Saída" class="text-xs whitespace-nowrap" />
                                            <input
                                                type="time"
                                                name="dias[{{ $numero }}][hora_fim]"
                                                value="{{ $h?->hora_fim ? substr($h->hora_fim, 0, 5) : '17:00' }}"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            >
                                        </div>

                                        @if ($h?->duracao)
                                            <span class="text-xs text-gray-400 ml-2">{{ $h->duracao }}</span>
                                        @endif
                                    </div>

                                    <p x-show="!ativo" class="text-sm text-gray-400 italic">Folga</p>
                                </div>

                                @error("dias.$numero.hora_fim")
                                    <p class="mt-1 ml-40 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>
                            Guardar horário
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
