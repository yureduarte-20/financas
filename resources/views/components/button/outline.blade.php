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
            'border-primary text-primary hover:bg-primary/10 focus:ring-primary',
        'secondary' =>
            'border-neutral-500 text-neutral-500 hover:bg-neutral-500/10 focus:ring-neutral-500',
        'accent' =>
            'border-accent text-accent hover:bg-accent/10 focus:ring-accent',
        'success' =>
            'border-success text-success hover:bg-success/10 focus:ring-success',
        'danger' =>
            'border-danger text-danger hover:bg-danger/10 focus:ring-danger',
        'warning' =>
            'border-warning text-warning hover:bg-warning/10 focus:ring-warning',
        'light' =>
            'border-white text-white hover:bg-white/10',
        'dark' =>
            'border-neutral-800 text-neutral-800 dark:border-neutral-400 dark:text-dark-muted hover:bg-neutral-800/10 dark:hover:bg-neutral-400/10',
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
