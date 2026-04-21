@props([
    'id' => 'select_'.md5(\Illuminate\Support\Str::random()),
    'label' => null
])
@php
    $name = $attributes->wire('model')?->value() ?? $attributes->get('name');
    $name == false and $name = $attributes->get('name');
@endphp
<div>
    @if($label)
        <label for="{{$id}}" class="block text-sm font-medium mb-2 dark:text-white">{{$label}}</label>
    @endif
    <select
            id="{{$id}}"
            {{$attributes->class([
                'py-3 px-4 pe-9 block w-full border-neutral-200 rounded-lg text-sm focus:border-primary focus:ring-primary disabled:opacity-50 disabled:pointer-events-none dark:bg-dark-bg dark:border-dark-border dark:text-dark-muted dark:focus:ring-neutral-600',
                'border-danger focus:border-danger focus:ring-danger' => $errors->has($attributes->get($name))
            ])}}
    >
        {{ $slot }}
    </select>
    @error($name)
    <p class="text-xs text-danger mt-2">{{ $message }}</p>
    @enderror
</div>