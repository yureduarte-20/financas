@props([
    'id' => 'id_' . md5(\Illuminate\Support\Str::random()),
    'label' => null,
    'leftLabel' => null
])
<div class="flex items-center gap-x-3">
    @if($leftLabel)
                <labe
        l            for="{{$id}}" class="text-sm text-gray-500 dark:text-dark-muted">{{$leftLabel}}</label>
    @endif

               <label for="{{$id}}" class="relative inline-block w-11 h-6 cursor-pointer">
        <input {{$attributes}} type="checkbox" id="{{$id}}" class="peer sr-only">
    <span class="absolute inset-0 bg-gray-200 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-primary dark:bg-neutral-700 dark:peer-checked:bg-primary peer-disabled:opacity-50 peer-disabled:pointer-events-none"></span>
        <span class="absolute top-1/2 start-0.5 -translate-y-1/2 size-5 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full dark:bg-neutral-400 dark:peer-checked:bg-white"></span>
    </label>
    @if($label)
        <label for="{{$id}}" class="text-sm text-gray-500 dark:text-dark-muted">{{$label}}</label>
    @endif
</div>