@props([
    'label' => '',
    'id' => 'id_'.md5(\Illuminate\Support\Str::random()),
    'icon' => null
])

@php
    $name = $attributes->wire('model')->value();
    $name == false and $name = $attributes->get('name');
    $required = $attributes->get('required');
    $disabled = $attributes->get('disabled');
    $readonly = $attributes->get('readonly');
    // Verifica se há erros de validação para este campo
    $hasError = $errors->has($name);
    // Classes base do input
    $inputClasses = 'py-2.5 sm:py-3 px-4 block w-full rounded-lg sm:text-sm dark:bg-neutral-800 dark:text-neutral-400';
    // Adiciona classes de borda com base na validação
    $inputClasses .= $hasError
        ? ' border-danger focus:border-danger focus:ring-danger'
        : ' border-neutral-200 focus:border-primary focus:ring-primary dark:border-neutral-700';
    // Adiciona classes para estado disabled/readonly
    $inputClasses .= $disabled || $readonly ? ' bg-gray-100 dark:bg-neutral-700' : '';
@endphp

<div {{ $attributes->only('class') }}>

    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium mb-2 dark:text-white">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            id="{{ $id }}"
            {{$attributes->merge( [
                'class'=> $inputClasses
            ])}}
            aria-describedby="{{ $name }}-error"
        >
        @if($hasError)
            <div class="absolute inset-y-0 end-0 flex items-center pointer-events-none pe-3">
                <svg class="shrink-0 size-4 text-danger" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" x2="12" y1="8" y2="12"></line>
                    <line x1="12" x2="12.01" y1="16" y2="16"></line>
                </svg>
            </div>

        @elseif($icon)
            <div class="absolute inset-y-0 end-0 flex items-center pointer-events-none pe-3">
                <i class="shrink-0 size-4 text-gray-400" >{{$icon}}</i>
            </div>
        @endif
    </div>
    @error($name)
        <p class="text-xs text-danger mt-2" id="{{ $name }}-error">
            {{ $message }}
        </p>
    @enderror
</div>
