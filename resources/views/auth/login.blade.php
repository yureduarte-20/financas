<x-auth-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-2">Bem-vindo de volta!</h1>
        <p class="text-[#706f6c] dark:text-[#A1A09A]">Entre com suas credenciais para acessar o painel.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <x-input
            id="email"
            class="block mt-1 w-full"
            type="email"
            name="email"
            :value="old('email')"
            required
            autofocus
            autocomplete="username"
            label="E-mail"
        />

        <!-- Password -->
        <x-input
            id="password"
            class="block mt-1 w-full"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            label="Senha"
        />

        <div class="flex items-center justify-between mt-4">
            <!-- Remember Me -->
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-primary shadow-sm focus:ring-primary" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Lembrar-me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-button full-width type="submit">
                Entrar
            </x-button>
        </div>
        
        <div class="mt-4 text-center">
            <span class="text-gray-600 dark:text-gray-400">Não tem uma conta?</span>
            <a href="{{ route('register') }}" class="text-primary hover:text-primary-hover font-medium">Cadastre-se</a>
        </div>
    </form>
</x-auth-layout>
