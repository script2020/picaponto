{{-- Shared task row partial. Expects $t (Tarefa with user, atribuidoA loaded) --}}
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
    $pessoa = $t->atribuidoA ?? $t->user;
@endphp
<div class="flex items-center gap-4">
    {{-- Avatar --}}
    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold flex items-center justify-center shrink-0">
        {{ strtoupper(substr($pessoa->name, 0, 1)) }}
    </div>

    {{-- Main info --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap mb-0.5">
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $chipsPrioridade[$t->prioridade] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $t->prioridade_label }}
            </span>
            @if ($t->projeto)
                <span class="text-xs text-gray-400 font-medium">{{ $t->projeto }}</span>
            @endif
        </div>
        <p class="text-sm font-medium text-gray-800 truncate">{{ $t->titulo }}</p>
        <p class="text-xs text-gray-400 mt-0.5">
            {{ $pessoa->name }}
            @if ($t->data)
                · {{ $t->data->format('d/m/Y') }}
            @endif
        </p>
    </div>

    {{-- Estado badge + chevron --}}
    <div class="flex items-center gap-3 shrink-0">
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $chipsEstado[$t->estado] ?? 'bg-gray-100 text-gray-600' }}">
            {{ $t->estado_label }}
        </span>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</div>
