<div>
    <x-errors class="mb-4" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
                Nova receita
            </h2>

            <form wire:submit="createIncome" class="space-y-4">
                <x-input label="Nome" wire:model="createForm.name" required maxlength="255" />

                <x-input label="Descricao" wire:model="createForm.description" maxlength="255" />

                <x-input label="Valor" x-mask:dynamic="$money($input, ',', '')" wire:model="createForm.value"
                    required />

                <x-input label="Data da receita" type="date" wire:model="createForm.expense_date" required />

                <x-select.native label="Categoria" wire:model="createForm.category_id" required>
                    <option value="">Selecione</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-select.native>

                <x-button type="submit" color="success">
                    Criar receita
                </x-button>
            </form>
        </div>



        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
                {{ $editing ? 'Editar receita' : 'Selecione uma receita para editar' }}
            </h2>

            @if ($editing)
                <form wire:submit="updateIncome" class="space-y-4">
                    <x-input label="Nome" wire:model="updateForm.name" required maxlength="255" />

                    <x-input label="Descricao" wire:model="updateForm.description" maxlength="255" />

                    <x-input label="Valor" type="number" step="0.01" min="0.01" wire:model="updateForm.value" required />

                    <x-input label="Data da receita" type="date" wire:model="updateForm.expense_date" required />

                    <x-select.native label="Categoria" wire:model="updateForm.category_id" required>
                        <option value="">Selecione</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-select.native>

                    <div class="flex gap-2">
                        <x-button type="submit" color="success">
                            Salvar alteracoes
                        </x-button>
                        <x-button.outline type="button" color="dark" wire:click="cancelEditing">
                            Cancelar
                        </x-button.outline>
                    </div>
                </form>
            @else
                <p class="text-sm text-gray-600 dark:text-dark-muted">
                    Clique em "Editar" na lista abaixo para carregar os dados da receita.
                </p>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold dark:text-dark-text">Filtros</h2>
            <x-button.outline type="button" color="dark" wire:click="resetFilters">
                Limpar filtros
            </x-button.outline>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-input label="Buscar" wire:model.live.debounce.300ms="search" placeholder="Nome ou descricao" />

            <x-select.native label="Categoria"  wire:model.blur.live="categoryFilter">
                <option value="">Todas</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-select.native>

            <x-input label="De" type="date"  wire:model.blur.live="dateFrom" />
            <x-input label="Ate" type="date"  wire:model.blur.live="dateTo" />
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <p class="text-sm text-gray-600 dark:text-dark-muted">Total das receitas filtradas</p>
            <p class="text-2xl font-semibold dark:text-dark-text">
                R$ {{ number_format($totalFilteredIncomes, 2, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <p class="text-sm text-gray-600 dark:text-dark-muted">Quantidade de receitas</p>
            <p class="text-2xl font-semibold dark:text-dark-text">{{ $incomesCount }}</p>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
            Receitas
        </h2>

        @if ($incomes->isEmpty())
            <p class="text-sm text-gray-600 dark:text-dark-muted">
                Nenhuma receita encontrada para os filtros selecionados.
            </p>
        @else
            <div class="space-y-3">
                @foreach ($incomes as $income)
                    <div
                        class="flex items-start justify-between gap-4 border border-surface-200 dark:border-dark-border rounded-lg p-3">
                        <div>
                            <p class="font-medium dark:text-dark-text">{{ $income->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-dark-muted">
                                {{ $income->category?->name ?? 'Sem categoria' }} - R$
                                {{ number_format($income->value, 2, ',', '.') }}
                            </p>
                            @if ($income->description)
                                <p class="text-sm text-gray-600 dark:text-dark-muted">
                                    {{ $income->description }}
                                </p>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-dark-muted">
                                {{ $income->expense_date?->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <x-button.outline type="button" color="primary" wire:click="startEditing('{{ $income->id }}')">
                                Editar
                            </x-button.outline>

                            <x-button type="button" color="danger" x-on:click="window.confirmDialog({
                                                    title: 'Tem certeza que deseja remover esta receita?',
                                                    description: 'Esta ação não pode ser desfeita.',
                                                    accept: () => {
                                                        $wire.call('deleteIncome', '{{ $income->id }}')
                                                    }
                                                })">
                                Excluir
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $incomes->links() }}
            </div>
        @endif
    </div>
</div>
