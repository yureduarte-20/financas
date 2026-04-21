<x-auth-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-2">Crie sua conta</h1>
        <p class="text-[#706f6c] dark:text-[#A1A09A]">Comece a controlar suas finanças hoje mesmo.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus
            autocomplete="name" label="Nome Completo" />

        <!-- Email Address -->
        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
            autocomplete="username" label="E-mail" />

        <!-- Password -->
        <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
            autocomplete="new-password" label="Senha" />

        <!-- Confirm Password -->
        <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation"
            required autocomplete="new-password" label="Confirmar Senha" />

        <div class="mt-6">
            <x-button full-width type="submit">
                Registrar
            </x-button>
        </div>

        <div class="mt-4 text-center">
            <span class="text-gray-600 dark:text-gray-400">Já tem uma conta?</span>
            <a href="{{ route('login') }}" class="text-primary hover:text-primary-hover font-medium">Entrar</a>
        </div>
    </form>
</x-auth-layout>