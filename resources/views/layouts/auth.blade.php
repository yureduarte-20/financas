<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FDFDFC] dark:bg-dark-bg">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
    <script>
        if (localStorage.getItem("hs_theme") === "dark" || (!("hs_theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-[#FDFDFC] dark:bg-dark-bg text-gray-900 flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col font-sans antialiased">
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col lg:max-w-4xl lg:flex-row">
                <!-- Branding Section -->
                <div class="bg-primary-100 dark:bg-primary-950 relative lg:-mr-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-l-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-24 h-24 text-primary dark:text-primary-400 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <h2 class="mt-4 text-2xl font-semibold text-primary dark:text-primary-300">FinançasPessoais</h2>
                        <p class="mt-2 text-sm text-primary opacity-80 dark:text-primary-400">Seu controle inteligente.</p>
                    </div>
                    <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-l-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] pointer-events-none"></div>
                </div>

                <!-- Form Section -->
                <div class="text-[13px] leading-[20px] flex-1 p-6 lg:p-12 bg-white dark:bg-dark-surface dark:text-dark-text shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tr-lg lg:rounded-bl-none z-10 flex flex-col justify-center">
                    {{ $slot }}
                </div>
            </main>
        </div>
        @livewireScripts
    </body>
</html>
