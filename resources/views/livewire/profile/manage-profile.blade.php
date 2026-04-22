<div class="space-y-6">
    <x-errors />

    <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-1 dark:text-dark-text">Informacoes da conta</h2>
        <p class="text-sm text-gray-600 dark:text-dark-muted mb-4">
            Atualize seu nome e e-mail de acesso.
        </p>

        @if (session('profile_success'))
            <x-alert type="success" title="Sucesso">
                {{ session('profile_success') }}
            </x-alert>
        @endif

        <form wire:submit="updateProfile" class="space-y-4 mt-4">
            <x-input label="Nome" wire:model="profileForm.name" maxlength="255" required />
            <x-input disabled label="E-mail" type="email" value="{{ Auth::user()->email }}" maxlength="255" required />

            <x-button type="submit" color="primary">
                Salvar perfil
            </x-button>
        </form>
    </div>

    <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-1 dark:text-dark-text">Trocar senha</h2>
        <p class="text-sm text-gray-600 dark:text-dark-muted mb-4">
            Informe sua senha atual para definir uma nova senha.
        </p>

        @if (session('password_success'))
            <x-alert type="success" title="Sucesso">
                {{ session('password_success') }}
            </x-alert>
        @endif

        <form wire:submit="updatePassword" class="space-y-4 mt-4">
            <x-input
                label="Senha atual"
                type="password"
                wire:model="passwordForm.current_password"
                autocomplete="current-password"
                required
            />

            <x-input
                label="Nova senha"
                type="password"
                wire:model="passwordForm.password"
                autocomplete="new-password"
                required
            />

            <x-input
                label="Confirmar nova senha"
                type="password"
                wire:model="passwordForm.password_confirmation"
                autocomplete="new-password"
                required
            />

            <x-button type="submit" color="primary">
                Alterar senha
            </x-button>
        </form>
    </div>
</div>
