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
        'primary' => 'bg-primary-600 hover:bg-primary-700 focus:bg-primary-700 text-white',
        'secondary' => 'bg-gray-500 hover:bg-gray-600 focus:bg-gray-600 text-white',
        'success' => 'bg-teal-500 hover:bg-teal-600 focus:bg-teal-600 text-white',
        'danger' => 'bg-red-500 hover:bg-red-600 focus:bg-red-600 text-white',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 focus:bg-yellow-600 text-white',
        'light' => 'bg-white hover:bg-gray-200 focus:bg-gray-200 text-gray-800',
        'dark' => 'bg-gray-800 hover:bg-gray-900 focus:bg-gray-900 text-white dark:bg-white dark:text-neutral-800',
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
