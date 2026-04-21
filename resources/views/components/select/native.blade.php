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
                'py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:focus:ring-neutral-600',
                'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has($attributes->get($name))
            ])}}
    >
        {{ $slot }}
    </select>
    @error($name)
    <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>