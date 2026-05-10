<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Horário Semanal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('sucesso'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('sucesso') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Clica num dia para definir o horário dessa data específica.
                </p>

                <div
                    id="calendario"
                    data-eventos-url="{{ route('horario.eventos') }}"
                    data-update-url="{{ url('/horario/__DATA__') }}"
                ></div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div id="modal-horario"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">

            <div class="flex items-center justify-between mb-5">
                <h3 id="modal-titulo" class="text-lg font-semibold text-gray-800"></h3>
                <button id="modal-fechar" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <form id="form-modal" method="POST" action="">
                @csrf
                @method('PUT')

                <label class="flex items-center gap-3 mb-5 cursor-pointer">
                    <input id="modal-ativo" type="checkbox" name="ativo" value="1"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">Dia de trabalho</span>
                </label>

                <div id="modal-campos-horas" class="space-y-4">
                    <div>
                        <x-input-label for="modal-inicio" value="Hora de entrada" />
                        <input id="modal-inicio" type="time" name="hora_inicio"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <x-input-label for="modal-fim" value="Hora de saída" />
                        <input id="modal-fim" type="time" name="hora_fim"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-3 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="modal-fechar-btn"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                        Cancelar
                    </button>
                    <x-primary-button type="submit">Guardar</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @vite('resources/js/horario.js')
</x-app-layout>
