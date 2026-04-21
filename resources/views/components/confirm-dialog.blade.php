@props(['name', 'show' => false, 'maxWidth' => '2xl', 'id' => '_' . md5(Str::random())])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];
@endphp

<div wire:ignore x-data="{
    show: @js($show),
    title: '',
    description: '',
    timeout: null,
    acceptCallback: () => {},
    rejectCallback: () => {},
    rejectLabel: '{{ __('Cancel')  }}',
    acceptLabel: '{{__('Ok')}}',
    openModal({ title = '', description = '', accept = () => {}, reject = () => {}, acceptLabel, rejectLabel }) {
        this.show = true;
        if (typeof accept == 'function') this.acceptCallback = accept;
        if (typeof reject == 'function') this.rejectCallback = reject;
        this.title = title;
        this.description = description;
        if(acceptLabel) this.acceptLabel = acceptLabel;
        if(rejectLabel) this.rejectLabel = rejectLabel;
    },
    accept() {
        this.acceptCallback();
        this.resetTransition();
    },
    reject() {
        this.rejectCallback();
        this.resetTransition();
    },
    resetTransition(){
        this.show = false;
        this.timeout = setTimeout(() => this.reset(), 500);
    },
    reset(){
        this.show = false;
        this.acceptCallback = () => {};
        this.rejectCallback = () =>{};
        this.title = '';
        this.description = '';
        this.acceptLabel = '{{__('Ok')}}';
        this.rejectLabel = '{{__('Cancel')}}';
        if(this.timeout) clearTimeout(this.timeout);
    },
    focusables() {
        // All focusable element types...
        let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
        return [...$el.querySelectorAll(selector)]
            // All non-disabled elements...
            .filter(el => !el.hasAttribute('disabled'))
    },
    firstFocusable() { return this.focusables()[0] },
    lastFocusable() { return this.focusables().slice(-1)[0] },
    nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
    prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
    nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
    prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1 },
}" x-init="$watch('show', value => {
    if (value) {
        document.body.classList.add('overflow-y-hidden');
        {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
    } else {
        document.body.classList.remove('overflow-y-hidden');
    }
})" x-on:open-modal.window="$event.detail.name == '{{ $name }}' && openModal($event.detail)"
    x-on:close-modal.window="$event.detail.name == '{{ $name }}' ? show = false : null"
    x-on:aceept-modal.window="$event.detail.name == '{{ $name }}' && aceept()"
    x-on:reject-modal.window="$event.detail.name == '{{ $name }}' && reject()" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()" x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" style="display: {{ $show ? 'block' : 'none' }};">

    <!-- Backdrop -->
    <div x-show="show" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="resetTransition"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <div x-show="show"
        class="mb-6 rounded-lg overflow-hidden transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto relative z-10"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="sm:max-w-lg sm:w-full m-3 sm:mx-auto">
            <div
                class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
                <div
                    class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                    <h3 id="{{ $id }}-label" class="font-bold text-gray-800 dark:text-white" x-text="title">
                    </h3>
                    <button type="button"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                        aria-label="Close" x-on:click="resetTransition()">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto">
                    <p class="mt-1 text-gray-800 dark:text-neutral-400" x-text="description"></p>
                </div>
                <div
                    class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                    <x-button color="danger" x-on:click="reject" x-text="rejectLabel" />
                    <x-button color="primary" type="button" x-on:click="accept" x-text="acceptLabel" />
                </div>
            </div>
        </div>
    </div>
</div>