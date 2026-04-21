<x-auth-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Redefinir Senha</h1>
        <p class="text-[#706f6c] dark:text-[#A1A09A]">Escolha uma nova senha para sua conta.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Address -->
        <x-input
            id="email"
            class="block mt-1 w-full"
            type="email"
            name="email"
            :value="old('email', $email)"
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
            autocomplete="new-password"
            label="Nova Senha"
        />

        <!-- Confirm Password -->
        <x-input
            id="password_confirmation"
            class="block mt-1 w-full"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
            label="Confirmar Nova Senha"
        />

        <div class="mt-6">
            <x-button full-width type="submit">
                Redefinir Senha
            </x-button>
        </div>
    </form>
</x-auth-layout>
