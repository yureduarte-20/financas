<div>
    @if (session('success'))
        <x-alert type="success" title="Sucesso">
            {{ session('success') }}
        </x-alert>
    @endif

    <x-errors class="mb-4" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
                Nova categoria
            </h2>

            <form wire:submit="createCategory" class="space-y-4">
                <x-input
                    label="Nome"
                    wire:model="createForm.name"
                    required
                    maxlength="255"
                />

                <x-input
                    label="Descricao"
                    wire:model="createForm.description"
                    maxlength="500"
                />

                <x-button type="submit" color="primary">
                    Criar categoria
                </x-button>
            </form>
        </div>

        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
                {{ $editing ? 'Editar categoria' : 'Selecione uma categoria para editar' }}
            </h2>

            @if ($editing)
                <form wire:submit="updateCategory" class="space-y-4">
                    <x-input
                        label="Nome"
                        wire:model="updateForm.name"
                        required
                        maxlength="255"
                    />

                    <x-input
                        label="Descricao"
                        wire:model="updateForm.description"
                        maxlength="500"
                    />

                    <div class="flex gap-2">
                        <x-button type="submit" color="primary">
                            Salvar alteracoes
                        </x-button>
                        <x-button.outline type="button" color="dark" wire:click="cancelEditing">
                            Cancelar
                        </x-button.outline>
                    </div>
                </form>
            @else
                <p class="text-sm text-gray-600 dark:text-dark-muted">
                    Clique em "Editar" na lista ao lado para carregar os dados da categoria.
                </p>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
            Minhas categorias
        </h2>

        @if ($categories->isEmpty())
            <p class="text-sm text-gray-600 dark:text-dark-muted">
                Nenhuma categoria cadastrada ainda.
            </p>
        @else
            <div class="space-y-3">
                @foreach ($categories as $category)
                    <div class="flex items-start justify-between gap-4 border border-surface-200 dark:border-dark-border rounded-lg p-3">
                        <div>
                            <p class="font-medium dark:text-dark-text">{{ $category->name }}</p>
                            @if ($category->description)
                                <p class="text-sm text-gray-600 dark:text-dark-muted">
                                    {{ $category->description }}
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <x-button.outline
                                type="button"
                                color="primary"
                                wire:click="startEditing('{{ $category->id }}')"
                            >
                                Editar
                            </x-button.outline>

                            <x-button
                                type="button"
                                color="danger"
                                x-on:click="window.confirmDialog({
                                    title: 'Tem certeza que deseja remover esta categoria?',
                                    description: 'Esta ação não pode ser desfeita.',
                                    accept: () => {
                                        $wire.call('deleteCategory', '{{ $category->id }}')
                                    }
                                })"
                                
                            >
                                Excluir
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
