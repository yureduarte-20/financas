@props([
    'id' => 'tabs_'.md5(\Illuminate\Support\Str::random()),
    'items' => [],
    'active' => null,
    'fullWidth' => false,
])

@php
    $normalizedItems = collect($items)
        ->map(function ($item, $index) {
            $id = (string) data_get($item, 'id', 'tab_'.$index);

            return [
                'id' => $id,
                'label' => (string) data_get($item, 'label', 'Tab '.($index + 1)),
                'disabled' => (bool) data_get($item, 'disabled', false),
            ];
        })
        ->values();

    $initialActive = $active;

    if (!$initialActive) {
        $initialActive = optional($normalizedItems->first(fn ($item) => !$item['disabled']))['id'];
    }

    $buttonBaseClasses = 'inline-flex items-center justify-center gap-2 whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:pointer-events-none disabled:opacity-50';
@endphp

<div
    x-data="{
        activeTab: @js($initialActive),
        tabs: @js($normalizedItems->pluck('id')->all()),
        isDisabled(tabId) {
            return !!this.$el.querySelector(`[data-tab-button='${tabId}']`)?.disabled;
        },
        activate(tabId) {
            if (this.isDisabled(tabId)) {
                return;
            }

            this.activeTab = tabId;
        },
        move(direction) {
            if (!this.tabs.length) {
                return;
            }

            const currentIndex = this.tabs.indexOf(this.activeTab);
            let nextIndex = currentIndex === -1 ? 0 : currentIndex;

            for (let i = 0; i < this.tabs.length; i++) {
                nextIndex = (nextIndex + direction + this.tabs.length) % this.tabs.length;
                const nextTabId = this.tabs[nextIndex];

                if (!this.isDisabled(nextTabId)) {
                    this.activeTab = nextTabId;
                    this.$nextTick(() => {
                        this.$el.querySelector(`[data-tab-button='${nextTabId}']`)?.focus();
                    });
                    break;
                }
            }
        },
    }"
    {{ $attributes->class(['w-full']) }}
>
    <nav
        class="flex border-b border-neutral-200 dark:border-dark-border"
        :class="{ 'w-full': true }"
        role="tablist"
        aria-orientation="horizontal"
    >
        @foreach ($normalizedItems as $tab)
            @php
                $tabId = $tab['id'];
                $disabled = $tab['disabled'];
            @endphp

            <button
                type="button"
                role="tab"
                data-tab-button="{{ $tabId }}"
                id="{{ $id }}-tab-{{ $tabId }}"
                aria-controls="{{ $id }}-panel-{{ $tabId }}"
                :aria-selected="activeTab === @js($tabId)"
                :tabindex="activeTab === @js($tabId) ? 0 : -1"
                @if ($disabled) disabled @endif
                x-on:click="activate(@js($tabId))"
                x-on:keydown.right.prevent="move(1)"
                x-on:keydown.left.prevent="move(-1)"
                class="{{ $buttonBaseClasses }}"
                :class="activeTab === @js($tabId)
                    ? 'border-primary text-primary dark:text-primary'
                    : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:text-dark-muted dark:hover:text-dark-text'"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </nav>

    <div class="mt-4">
        @foreach ($normalizedItems as $tab)
            @php
                $tabId = $tab['id'];
                $panelSlotName = 'panel_'.$tabId;
            @endphp

            <section
                x-cloak
                x-show="activeTab === @js($tabId)"
                x-transition.opacity.duration.150ms
                role="tabpanel"
                tabindex="0"
                id="{{ $id }}-panel-{{ $tabId }}"
                aria-labelledby="{{ $id }}-tab-{{ $tabId }}"
                class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-md"
            >
                @if (isset($$panelSlotName))
                    {{ $$panelSlotName }}
                @elseif (array_key_exists('content', $tab))
                    {!! $tab['content'] !!}
                @endif
            </section>
        @endforeach
    </div>
</div>
