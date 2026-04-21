@props([
    'type' => 'info', // Padrão: info (pode ser: info, success, warning, error, dark, gray)
    'duration' => 5000, // Duração em milissegundos (0 = não fecha automaticamente)
    'position' => 'right', // Posição na tela (right, left, center)
])

<div x-data="toast({
    type: '{{$type}}',
    duration: {{$duration}},
    position: '{{$position}}'
})" x-init="init()" x-on:show-toast.window="open($event.detail)"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-full"
    x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 transform translate-x-full"
    :class="{
        'right-4': position === 'right',
        'left-4': position === 'left',
        'left-1/2 transform -translate-x-1/2': position === 'center'
    }"
    class="fixed z-50 top-5">
    <template x-for="not of notifications">
        <div x-bind:class="`max-w-xs text-sm text-white rounded-xl shadow-lg ${getBgClass(not.id)} mb-2`" role="alert"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full" tabindex="-1">
            <div class="flex p-4">
                <span x-text="not.message"></span>
                <div class="ms-auto">
                    
                    <button x-on:click="close(not.id)" type="button"
                        class="inline-flex shrink-0 justify-center items-center size-5 rounded-lg text-white hover:text-white opacity-50 hover:opacity-100 focus:outline-hidden focus:opacity-100"
                        aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>