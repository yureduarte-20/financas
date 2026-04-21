@props([
    'type' => 'button',
    'color' => 'primary',
    'size' => 'medium',
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'fullWidth' => false,
    'label' => null
])
@php
    // Mapeamento de cores para classes CSS
    $colorClasses = [
        'primary' => 'bg-primary hover:bg-primary-hover text-primary-foreground',
        'secondary' => 'bg-neutral-500 hover:bg-neutral-600 text-white',
        'accent' => 'bg-accent hover:bg-accent-hover text-accent-foreground',
        'success' => 'bg-success hover:bg-success-hover text-success-foreground',
        'danger' => 'bg-danger hover:bg-danger-hover text-danger-foreground',
        'warning' => 'bg-warning hover:bg-warning-hover text-warning-foreground',
        'light' => 'bg-white hover:bg-gray-200 text-gray-800',
        'dark' => 'bg-neutral-800 hover:bg-neutral-900 text-white dark:bg-dark-text dark:text-neutral-800',
    ];

    // Mapeamento de tamanhos
    $sizeClasses = [
        'small' => 'py-2 px-3 text-xs',
        'medium' => 'py-3 px-4 text-sm',
        'large' => 'py-4 px-5 text-base',
    ];

    // Classes base
    $baseClasses =
        'inline-flex items-center gap-x-2 font-medium rounded-lg border border-transparent focus:outline-none disabled:opacity-50 disabled:pointer-events-none transition-colors duration-150';
    if (!$icon)
        $baseClasses .= ' justify-center ';
    // Combina todas as classes
    $classes = implode(' ', [
        $baseClasses,
        $colorClasses[$color] ?? $colorClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['medium'],
        $fullWidth ? 'w-full justify-center' : '',
    ]);
@endphp
<button x-data type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon && $iconPosition === 'left')
        <i class="w-4 h-4">{{ $icon }}</i>
    @endif

    {{ $label ?? $slot }}

    @if ($icon && $iconPosition === 'right')
        <i class="w-4 h-4">{{ $icon }}</i>
    @endif
</button>
