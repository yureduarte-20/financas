<x-app-layout>
    <x-slot name="header">
        Component Style Guide
    </x-slot>

    <div class="space-y-12">
        <!-- Typography & Colors -->
        <section>
            <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 dark:border-dark-border pb-2">Identidade & Cores</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-primary text-white shadow-sm">
                    <p class="font-bold">Primary</p>
                    <p class="text-xs opacity-80">--color-primary</p>
                </div>
                <div class="p-4 rounded-xl bg-accent text-white shadow-sm">
                    <p class="font-bold">Accent</p>
                    <p class="text-xs opacity-80">--color-accent</p>
                </div>
                <div class="p-4 rounded-xl bg-success text-white shadow-sm">
                    <p class="font-bold">Success</p>
                    <p class="text-xs opacity-80">--color-success</p>
                </div>
                <div class="p-4 rounded-xl bg-danger text-white shadow-sm">
                    <p class="font-bold">Danger</p>
                    <p class="text-xs opacity-80">--color-danger</p>
                </div>
                <div class="p-4 rounded-xl bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border shadow-sm">
                    <p class="font-bold dark:text-dark-text">Surface</p>
                    <p class="text-xs text-gray-500 dark:text-dark-muted">Adaptive Card</p>
                </div>
            </div>
        </section>

        <!-- Buttons -->
        <section>
            <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 dark:border-dark-border pb-2">Botões (x-button)</h2>
            <div class="space-y-8">
                <!-- Solid Variants -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 dark:text-dark-muted">Solid Variants</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-button color="primary">Primary</x-button>
                        <x-button color="accent">Accent</x-button>
                        <x-button color="success">Success</x-button>
                        <x-button color="danger">Danger</x-button>
                        <x-button color="warning">Warning</x-button>
                        <x-button color="dark">Dark</x-button>
                        <x-button color="light">Light</x-button>
                    </div>
                </div>

                <!-- Sizes -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 dark:text-dark-muted">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-button size="xs">Extra Small</x-button>
                        <x-button size="sm">Small</x-button>
                        <x-button size="md">Medium</x-button>
                        <x-button size="lg">Large</x-button>
                        <x-button size="xl">Extra Large</x-button>
                    </div>
                </div>

                <!-- Outline Variants -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 dark:text-dark-muted">Outline Variants</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-button.outline color="primary">Primary</x-button.outline>
                        <x-button.outline color="accent">Accent</x-button.outline>
                        <x-button.outline color="success">Success</x-button.outline>
                        <x-button.outline color="danger">Danger</x-button.outline>
                        <x-button.outline color="dark">Dark Outline</x-button.outline>
                    </div>
                </div>
            </div>
        </section>

        <!-- Forms -->
        <section>
            <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 dark:border-dark-border pb-2">Formulários & Inputs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <x-input label="Nome Completo" placeholder="Digite seu nome" required />
                    <x-input label="E-mail" type="email" placeholder="email@exemplo.com" icon="m" />
                    <x-input label="Campo com Erro" placeholder="Erro de teste" />
                    @php
                        // Simulando erro na view para teste visual
                        $errors->add('test_error', 'Este é um exemplo de mensagem de erro de validação.');
                    @endphp
                    <x-input name="test_error" label="Input Protegido" placeholder="Tente digitar algo..." disabled />
                </div>
                <div class="space-y-6">
                    <x-select.native label="Categoria (Nativo)">
                        <option>Alimentação</option>
                        <option>Transporte</option>
                        <option>Lazer</option>
                    </x-select.native>

                    <x-switch label="Receber notificações por e-mail" id="switch-test" checked />
                    <x-switch leftLabel="Modo Offline" id="switch-test-2" />

                    <div class="p-4 bg-gray-50 dark:bg-dark-surface rounded-xl border border-gray-200 dark:border-dark-border">
                        <p class="text-sm font-medium mb-3 dark:text-dark-text">Exemplo de Dropdown</p>
                        <x-dropdown>
                            <x-slot name="trigger">
                                <x-button.outline color="dark" size="sm">Opções de Conta</x-button.outline>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="#">Meu Perfil</x-dropdown-link>
                                <x-dropdown-link href="#">Configurações</x-dropdown-link>
                                <div class="border-t border-gray-100 dark:border-dark-border"></div>
                                <x-dropdown-link href="#" class="text-danger">Sair</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Feedback -->
        <section>
            <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 dark:border-dark-border pb-2">Feedback & Interação</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-600 dark:text-dark-muted">Alertas Estáticos (x-alert)</h3>
                    <x-alert type="info">Informativo: O sistema será reiniciado à meia-noite.</x-alert>
                    <x-alert type="success">Sucesso: Os dados foram processados pela IA.</x-alert>
                    <x-alert type="warning">Aviso: Você atingiu 80% do seu orçamento mensal.</x-alert>
                    <x-alert type="danger">Erro: Não foi possível ler o arquivo enviado.</x-alert>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-600 dark:text-dark-muted">Ações Dinâmicas</h3>
                    <div class="flex flex-wrap gap-4">
                        <!-- Test Toast Button -->
                        <x-button color="success" size="sm" 
                            onclick="window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Operação realizada com sucesso!', type: 'success' } }))">
                            Disparar Toast Sucesso
                        </x-button>

                        <x-button color="danger" size="sm" 
                            onclick="window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Erro ao salvar alterações.', type: 'error' } }))">
                            Disparar Toast Erro
                        </x-button>

                        <!-- Test Modal Button -->
                        <x-button color="primary" size="sm" 
                            x-on:click="$dispatch('open-modal', { 
                                name: 'confirm-dialog', 
                                title: 'Confirmar Ação', 
                                description: 'Você tem certeza que deseja realizar esta operação de teste?',
                                accept: () => alert('Ação aceita!')
                            })">
                            Abrir Modal de Confirmação
                        </x-button>
                    </div>
                    
                    <div class="mt-6">
                        <x-errors />
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
