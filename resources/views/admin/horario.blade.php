<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Horário de {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
            </div>
            <a href="{{ route('registos.admin') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                ← Painel Admin
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div
                    id="calendario"
                    data-eventos-url="{{ route('horario.admin.eventos', $user) }}"
                    data-readonly="true"
                ></div>
            </div>
        </div>
    </div>

    @vite('resources/js/horario.js')
</x-app-layout>
