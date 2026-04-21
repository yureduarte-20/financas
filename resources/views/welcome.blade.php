<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'FinançasPessoais') }} - IA para suas finanças</title>
    <script>
        if (localStorage.getItem("hs_theme") === "dark" || (!("hs_theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-dark-bg text-gray-900 dark:text-gray-100 font-sans antialiased">
    <!-- Header -->
    <header class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-dark-bg/80 backdrop-blur-md border-b border-gray-200 dark:border-dark-border">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span class="font-bold text-xl tracking-tight">FinançasPessoais</span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-primary transition-colors">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium hover:text-primary transition-colors">Entrar</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-hover transition-colors shadow-sm">
                            Começar
                        </a>
                    @endif
                @endauth
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main>
        <div class="relative overflow-hidden pt-32 pb-20 lg:pt-48 lg:pb-32 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Background Decorations -->
            <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-[800px] h-[400px] opacity-30 dark:opacity-20 pointer-events-none blur-3xl">
                <div class="absolute inset-0 bg-gradient-to-r from-primary to-accent-400 rounded-full mix-blend-multiply flex-1"></div>
            </div>

            <div class="relative flex flex-col lg:flex-row items-center gap-16">
                <!-- Text Content -->
                <div class="flex-1 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent-50 dark:bg-dark-accent text-primary text-sm font-semibold mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-primary"></span>
                        Alimentado por IA (Claude LLM)
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
                        Transforme o caos financeiro em <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent-500">clareza absoluta.</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 dark:text-dark-muted mb-8 max-w-2xl mx-auto lg:mx-0">
                        Faça upload de recibos e faturas. Nós lemos, extraímos os dados, sugerimos a categoria e organizamos seus gastos automaticamente utilizando Inteligência Artificial.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3 text-base font-medium text-white bg-primary rounded-xl hover:bg-primary-hover transition-all focus:ring-4 focus:ring-primary/30 shadow-lg shadow-primary/20">
                            Criar Conta Grátis
                        </a>
                        <a href="#features" class="w-full sm:w-auto px-8 py-3 text-base font-medium text-gray-700 dark:text-dark-text bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700 transition-all">
                            Como Funciona
                        </a>
                    </div>
                </div>

                <!-- Mockup Visual -->
                <div class="flex-1 w-full max-w-lg lg:max-w-none relative">
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent blur-2xl rounded-full"></div>
                    <div class="relative bg-white dark:bg-dark-surface rounded-2xl border border-gray-200 dark:border-dark-border shadow-2xl p-6 overflow-hidden">
                        <!-- Toolbar -->
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="ml-4 h-4 w-32 bg-gray-100 dark:bg-dark-surface rounded"></div>
                        </div>

                        <!-- Content mock -->
                        <div class="space-y-4">
                            <div class="h-32 bg-gray-50 dark:bg-dark-bg border border-dashed border-gray-300 dark:border-dark-border rounded-xl flex items-center justify-center">
                                <div class="text-center text-gray-400 dark:text-dark-muted text-sm flex flex-col items-center">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    Enviando Recibo_Uber.pdf
                                    <div class="w-24 h-1 bg-primary rounded-full mt-3"></div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-dark-surface/50 rounded-xl p-4 border border-gray-100 dark:border-dark-border">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-black flex items-center justify-center text-white font-bold text-xs">UB</div>
                                        <div>
                                            <div class="font-medium text-sm">Uber *Viagem</div>
                                            <div class="text-xs text-gray-500 dark:text-dark-muted">21 Abr 2026</div>
                                        </div>
                                    </div>
                                    <div class="font-bold text-primary">- R$ 32,50</div>
                                </div>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200 dark:border-dark-border">
                                    <span class="text-xs text-gray-500 dark:text-dark-muted flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        Sugerido pela IA
                                    </span>
                                    <span class="inline-flex px-2 py-1 bg-info-100 dark:bg-blue-900/30 text-info-700 dark:text-info-400 text-xs rounded-md">Transporte</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="py-24 bg-white dark:bg-[#111110]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">Tudo funciona no piloto automático</h2>
                    <p class="text-lg text-gray-600 dark:text-dark-muted max-w-2xl mx-auto">
                        Nós focamos na tecnologia para que você foque no que importa. Nosso sistema aprende com seus hábitos financeiros.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-gray-50 dark:bg-dark-bg rounded-2xl p-6 border border-gray-100 dark:border-dark-border hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-accent-50 dark:bg-dark-accent rounded-xl flex items-center justify-center text-primary mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Leitura Pela IA</h3>
                        <p class="text-sm text-gray-600 dark:text-dark-muted">
                            Faça o upload de PDF ou imagens de suas notas. Nossa inteligência decodifica valores, datas e estabelecimentos.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-gray-50 dark:bg-dark-bg rounded-2xl p-6 border border-gray-100 dark:border-dark-border hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-info-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-info-400 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Categorização Automática</h3>
                        <p class="text-sm text-gray-600 dark:text-dark-muted">
                            Nós entendemos que o "Mercado XYZ" deve ir para "Alimentação". O sistema sugere a categoria ideal.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-gray-50 dark:bg-dark-bg rounded-2xl p-6 border border-gray-100 dark:border-dark-border hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center text-green-600 dark:text-green-400 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Dashboards Dinâmicos</h3>
                        <p class="text-sm text-gray-600 dark:text-dark-muted">
                            Gere insights rápidos do seu mês com gráficos que convertem números em fáceis interpretações visuais.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-gray-50 dark:bg-dark-bg rounded-2xl p-6 border border-gray-100 dark:border-dark-border hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Alertas de Orçamento</h3>
                        <p class="text-sm text-gray-600 dark:text-dark-muted">
                            Defina limites por categoria. Alertamos ativamente assim que você superar os limites definidos.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-dark-bg border-t border-gray-200 dark:border-dark-border py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span class="font-bold text-lg tracking-tight">FinançasPessoais</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-dark-muted">
                &copy; {{ date('Y') }} FinançasPessoais. Todos os direitos reservados.
            </p>
        </div>
    </footer>
</body>
</html>
