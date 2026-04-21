<x-auth-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Esqueceu sua senha?</h1>
        <p class="text-[#706f6c] dark:text-[#A1A09A]">
            Sem problemas. Apenas informe seu endereço de e-mail e nós enviaremos um link de redefinição de senha para você.
        </p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-success dark:text-success-400">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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
            label="E-mail"
        />

        <div class="mt-6">
            <x-button full-width type="submit">
                Link de Redefinição de Senha
            </x-button>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium">Voltar para o login</a>
        </div>
    </form>
</x-auth-layout>
