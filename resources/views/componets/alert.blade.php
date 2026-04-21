@props([
    'type' => 'info',
    'title' => '',
    'dismissible' => true,
    'icon' => null,
])

@php
    // Mapeamento de tipos para classes CSS
    $typeClasses = [
        'dark' => 'bg-gray-800 dark:bg-white dark:text-neutral-800',
        'secondary' => 'bg-gray-500',
        'info' => 'bg-blue-600 dark:bg-blue-500',
        'success' => 'bg-teal-500',
        'success' => 'bg-teal-500',
        'danger' => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'light' => 'bg-white text-gray-600',
    ];

    // Classes base comuns a todos os alertas
    $baseClasses = 'mt-2 text-sm rounded-lg p-4 text-white flex items-start gap-2';

    // Classes específicas do tipo
    $typeClass = $typeClasses[$type] ?? $typeClasses['info'];

    // Classes para o ícone (se fornecido)
    $iconClasses = 'h-5 w-5 flex-shrink-0';

    // Título padrão baseado no tipo se não fornecido
    $title = $title ?: ucfirst($type);
@endphp

<div
        {{ $attributes->merge([
            'class' => "$baseClasses $typeClass",
            'role' => 'alert',
            'tabindex' => '-1',
            'aria-labelledby' => "alert-{$type}-label",
        ]) }}
>
    @if($icon)
        <div class="{{ $iconClasses }}">
            {{ $icon }}
        </div>
    @endif

    <div class="flex-1">
        @if($title)
            <span id="alert-{{ $type }}-label" class="font-bold block mb-1">{{ $title }}</span>
        @endif

        {{ $slot }}
    </div>

    @if($dismissible)
        <button
                type="button"
                class="ml-auto -mx-1.5 -my-1.5 p-1.5 rounded-lg focus:ring-2 focus:ring-current hover:bg-opacity-20 hover:bg-white"
                aria-label="Fechar"
                @click="$el.parentElement.remove()"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>