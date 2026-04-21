@props([
    'type' => 'button',
    'color' => 'primary', // primary, secondary, success, danger, warning, light, dark
    'size' => 'medium', // small, medium, large
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'fullWidth' => false,
])

@php
    // Mapeamento de cores para classes CSS (estilo outline)
    $colorClasses = [
        'primary' =>
            'border-blue-600 text-blue-600 hover:border-blue-500 hover:text-blue-500 focus:border-blue-500 focus:text-blue-500 dark:border-blue-500 dark:text-blue-500 dark:hover:text-blue-400 dark:hover:border-blue-400',
        'secondary' =>
            'border-gray-500 text-gray-500 hover:border-gray-800 hover:text-gray-800 focus:border-gray-800 focus:text-gray-800 dark:border-neutral-400 dark:text-neutral-400 dark:hover:text-neutral-300 dark:hover:border-neutral-300',
        'success' =>
            'border-teal-500 text-teal-500 hover:border-teal-400 hover:text-teal-400 focus:border-teal-400 focus:text-teal-400',
        'danger' =>
            'border-red-500 text-red-500 hover:border-red-400 hover:text-red-400 focus:border-red-400 focus:text-red-400',
        'warning' =>
            'border-yellow-500 text-yellow-500 hover:border-yellow-400 hover:text-yellow-400 focus:border-yellow-400 focus:text-yellow-400',
        'light' =>
            'border border-white text-white hover:border-white/70 hover:text-white/70 focus:border-white/70 focus:text-white/70',
        'dark' =>
            'border border-gray-500 text-gray-500 hover:border-gray-800 hover:text-gray-800 focus:outline-hidden focus:border-gray-800 focus:text-gray-800 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-400 dark:text-neutral-400 dark:hover:text-neutral-300 dark:hover:border-neutral-300',
    ];

    // Mapeamento de tamanhos (mesmo do componente anterior)
    $sizeClasses = [
        'small' => 'py-2 px-3 text-xs',
        'medium' => 'py-3 px-4 text-sm',
        'large' => 'py-4 px-5 text-base',
    ];

    // Classes base
    $baseClasses =
        'inline-flex items-center gap-x-2 font-medium rounded-lg focus:outline-none disabled:opacity-50 disabled:pointer-events-none transition-colors duration-150 bg-transparent';

    // Combina todas as classes
    $classes = implode(' ', [
        $baseClasses,
        $colorClasses[$color] ?? $colorClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['medium'],
        $fullWidth ? 'w-full justify-center' : '',
    ]);
@endphp

<button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon && $iconPosition === 'left')
        <i class="w-4 h-4">{{ $icon }}</i>
    @endif

    {{ $slot }}

    @if ($icon && $iconPosition === 'right')
        <i class="w-4 h-4">{{ $icon }}</i>
    @endif
</button>
