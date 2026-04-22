@props(['active'])

<div x-data="{ 
        sidebarOpen: window.innerWidth >= 768,
        isMobile: window.innerWidth < 768
    }" x-init="window.addEventListener('resize', () => { 
        sidebarOpen = window.innerWidth >= 768;
        isMobile = window.innerWidth < 768;
    })" class="flex h-screen bg-surface-50 dark:bg-dark-bg">

    <!-- Sidebar -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" :class="{
             'fixed inset-y-0 left-0 z-50 w-64': isMobile,
             'relative w-64 flex-shrink-0': !isMobile
         }" class="bg-white dark:bg-dark-surface shadow-lg border-r border-surface-200 dark:border-dark-border">

        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-surface-200 dark:border-dark-border">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center">
                <x-application-logo class="h-8 w-auto text-primary" />
                <span class="ml-3 text-xl font-semibold text-gray-800 dark:text-dark-text">{{ config('app.name', 'Laravel') }}</span>
            </a>
            
            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <div x-data="{ 
                    theme: localStorage.getItem('hs_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                    toggleTheme() {
                        this.theme = this.theme === 'dark' ? 'light' : 'dark';
                        localStorage.setItem('hs_theme', this.theme);
                        if (this.theme === 'dark') {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                }">
                    <button @click="toggleTheme()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-dark-muted dark:hover:bg-dark-surface-hover transition-colors focus:outline-none" aria-label="Trocar tema">
                        <!-- Sun Icon (visible on dark) -->
                        <svg x-show="theme === 'dark'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <path d="M12 2v2"></path>
                            <path d="M12 20v2"></path>
                            <path d="m4.93 4.93 1.41 1.41"></path>
                            <path d="m17.66 17.66 1.41 1.41"></path>
                            <path d="M2 12h2"></path>
                            <path d="M20 12h2"></path>
                            <path d="m6.34 17.66-1.41 1.41"></path>
                            <path d="m19.07 4.93-1.41 1.41"></path>
                        </svg>
                        <!-- Moon Icon (visible on light) -->
                        <svg x-show="theme !== 'dark'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mobile close button -->
                <button @click="sidebarOpen = false" class="md:hidden text-gray-500 hover:text-gray-700 dark:text-dark-muted dark:hover:text-dark-text focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="mt-6 px-3">
            <div class="space-y-1">
                <!-- Dashboard Link -->
                <a href="{{ route('dashboard') }}" wire:navigate @class([
                    'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                    'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('dashboard'),
                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-dark-surface-hover/50 dark:hover:text-neutral-100' => !request()->routeIs('dashboard'),
                ])>
    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('Dashboard') }}
                </a>



                <!-- Categories Link -->
                <a href="{{ route('categories.index') }}" wire:navigate @class([
                    'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                    'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('categories.*'),
                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-dark-surface-hover/50 dark:hover:text-neutral-100' => !request()->routeIs('categories.*'),
                ])>
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    {{ __('Categorias') }}
                </a>

                <!-- Places Link -->
                <!-- <a href="#" wire:navigate @class([
                    'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                    'bg-indigo-50 border-indigo-500 text-indigo-700' => request()->routeIs('places.*'),
                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent' => !request()->routeIs('places.*'),
                ])>
             <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ __('Places') }}
                </a> -->

                <!-- Transactions Link -->
                @can('view work orders')
                    <a href="{{ route('work-orders.index') }}" wire:navigate @class([
                        'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                        'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('work-orders.*'),
                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-dark-surface-hover/50 dark:hover:text-neutral-100' => !request()->routeIs('work-orders.*'),
                    ])>
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        {{ __('Work Orders') }}
                    </a>
                @endcan
                @can('view requesters')
                    <a href="{{ route('requesters.index') }}" wire:navigate @class([
                        'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                        'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('requesters.*'),
                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-dark-surface-hover/50 dark:hover:text-neutral-100' => !request()->routeIs('requesters.*'),
                    ])>
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        {{ __('Clientes') }}
                    </a>
                @endcan
                @can('view spare parts')
                    <!-- Spare Parts Link -->
                    <a href="{{ route('spare-parts.index') }}" wire:navigate @class([
                        'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                        'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('spare-parts.*'),
                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-dark-surface-hover/50 dark:hover:text-neutral-100' => !request()->routeIs('spare-parts.*'),
                    ])>
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                        {{ __('Spare Parts') }}
                    </a>
                @endcan
                @can('view users')
                    <a href="{{ route('users.index') }}" wire:navigate @class([
                        'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                        'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('users.*'),
                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-dark-surface-hover/50 dark:hover:text-neutral-100' => !request()->routeIs('users.*'),
                    ])>
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        {{ __('Usuários') }}
                    </a>
                @endcan
            </div>


            <!-- Additional sections can be added here -->

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-dark-border">
                <div class="px-3">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-dark-muted uppercase tracking-wider">
                        {{ __('Administration') }}
                    </h3>
                </div>

                <div class="mt-1 space-y-1">
                    <!-- Profile Link -->
                    <a href="{{ route('profile') }}" wire:navigate @class([
                        'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                        'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('profile'),
                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-neutral-800/50 dark:hover:text-neutral-100' => !request()->routeIs('profile'),
                    ])>
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Profile') }}
                    </a>

                    <!-- Style Guide Link -->
                    <a href="{{ route('style-guide') }}" wire:navigate @class([
                        'flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                        'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/50 dark:border-primary-400 dark:text-primary-300' => request()->routeIs('style-guide'),
                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent dark:text-dark-muted dark:hover:bg-neutral-800/50 dark:hover:text-neutral-100' => !request()->routeIs('style-guide'),
                    ])>
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        {{ __('Style Guide') }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="absolute bottom-0 w-full p-4 border-t border-gray-200 dark:border-dark-border">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div
                        class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center text-white font-medium">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-dark-muted"
                        x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                        x-on:profile-updated.window="name = $event.detail.name"></p>
                    <p class="text-xs text-gray-500 dark:text-dark-muted">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="mt-3 w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-dark-muted hover:text-gray-900 dark:hover:text-neutral-100 hover:bg-surface-100 dark:hover:bg-dark-surface-hover rounded-md transition-colors duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
            <!-- Logout Button -->

        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden relative z-10">
        <!-- Top Navigation Bar -->
        <header class="bg-white dark:bg-dark-surface shadow-sm border-b border-gray-200 dark:border-dark-border">
            <div class="flex items-center justify-between h-16 px-6">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true"
                    class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Page Title -->
                <div class="flex-1">
                    @if (isset($header))
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-dark-text">{{ $header }}</h1>
                    @endif
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div>
                        {{--
                        <livewire:notifications /> --}}
                    </div>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <div
                                    class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center text-white font-medium">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="ml-2 text-gray-700 dark:text-dark-muted hidden sm:block"
                                    x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                    x-on:profile-updated.window="name = $event.detail.name"></span>
                                <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link href="{{ route('profile') }}" wire:navigate>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>

                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto bg-surface-50 dark:bg-dark-bg">
            <div class="max-w-8xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div x-show="sidebarOpen && isMobile" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 md:hidden">
    </div>
</div>