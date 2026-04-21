<x-auth-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-2">Autenticação de Dois Fatores</h1>
        <p class="text-gray-600 dark:text-dark-muted">
            Para sua segurança, enviamos um código de acesso para <strong>{{ $email }}</strong>.
        </p>
    </div>

    @if (session('status') === 'code-sent')
        <div class="mb-4 font-medium text-sm text-success">
            Um novo código foi enviado para o seu e-mail.
        </div>
    @endif

    <form method="POST" action="{{ route('auth.verify.login.post') }}" class="space-y-6">
        @csrf

        <x-input 
            id="code" 
            class="block mt-1 w-full text-center text-2xl tracking-[1em] font-mono" 
            type="text" 
            name="code" 
            placeholder="000000"
            required 
            autofocus 
            label="Código de Segurança"
        />

        <div class="mt-6">
            <x-button full-width type="submit">
                Confirmar Acesso
            </x-button>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-dark-border text-center">
        <p class="text-sm text-gray-600 dark:text-dark-muted mb-4">
            Teve algum problema com o e-mail?
        </p>
        
        <form method="POST" action="{{ route('auth.verify.login.resend') }}">
            @csrf
            <button type="submit" class="text-primary hover:text-primary-hover font-medium text-sm transition-colors">
                Reenviar código (disponível em 60s)
            </button>
        </form>
        
        <div class="mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-dark-text underline underline-offset-4">
                Voltar para o Login
            </a>
        </div>
    </div>
</x-auth-layout>
