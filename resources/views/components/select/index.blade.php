@props([
    'id' => 'id_'.md5(\Illuminate\Support\Str::random()),
    'placeholder' => __('Select a Option'),
    'label' => null,
    'options' => []
])
@php
    $name = $attributes->wire('model')->value();
    $name == false and $name = $attributes->get('name');

    $commonToggleClasses = ' hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative py-3 px-4 pe-9 flex text-nowrap w-full cursor-pointer bg-white border rounded-lg text-start text-sm dark:bg-neutral-900 dark:text-neutral-400';
    $errorToggleClasses = ' border-red-500 focus:border-red-500 focus:ring-red-500';
    $defaultToggleClasses = ' border-gray-200 focus:outline-hidden focus:ring-2 focus:ring-blue-500 dark:border-neutral-700 dark:focus:outline-hidden dark:focus:ring-1 dark:focus:ring-neutral-600';
    $errors->has($name) ? $commonToggleClasses .= $errorToggleClasses : $commonToggleClasses .= $defaultToggleClasses;
    $defaultOptions = array_merge([
    'placeholder' => $placeholder,
    'toggleTag' => '<button type="button" aria-expanded="false"></button>',
    'hasSearch' => true,
    'minSearchLength' => 3,
    'searchPlaceholder' => ucfirst(__('search')) . '...',
    'searchClasses' => 'block w-full sm:text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 before:absolute before:inset-0 before:z-1 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 py-1.5 sm:py-2 px-3',
    'searchWrapperClasses' => 'bg-white p-2 -mx-1 sticky top-0 dark:bg-neutral-900',
    'toggleClasses' => trim($commonToggleClasses),
    'dropdownClasses' => 'mt-2 z-50 w-full max-h-72 p-1 space-y-0.5 bg-white border border-gray-200 rounded-lg overflow-hidden overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 dark:bg-neutral-900 dark:border-neutral-700',
    'optionClasses' => 'py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-hidden focus:bg-gray-100 hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 dark:bg-neutral-900 dark:hover:bg-neutral-800 dark:text-neutral-200 dark:focus:bg-neutral-800',
    'optionTemplate' => '<div class="flex justify-between items-center w-full"><span data-title></span><span class="hidden hs-selected:block"><svg class="shrink-0 size-3.5 text-blue-600 dark:text-blue-500 " xmlns="http:.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span></div>',
    'extraMarkup' => '<div class="absolute top-1/2 end-3 -translate-y-1/2"><svg class="shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 " xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg></div>',
], $options);
@endphp
<div wire:ignore.self id="container-{{$id}}">
    @if($label)
        <label for="{{$id}}" class="block text-sm font-medium mb-2 dark:text-white">{{$label}}</label>
    @endif
    <select x-init="window.initPreline()" {{ $attributes }} id="{{$id}}" data-hs-select='@json($defaultOptions)'
            class="hidden">
        {{$slot}}
    </select>
    @error($name)
    <p class="text-sm text-red-600 mt-2">{{$message}}</p>
    @enderror
</div>
