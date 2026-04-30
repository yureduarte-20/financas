<div>
    <x-errors class="mb-4" />

    <!-- Date Filter -->
    <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold dark:text-dark-text">Período</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input label="De" type="date"  wire:model.blur.live="dateFrom" />
            <x-input label="Até" type="date"  wire:model.blur.live="dateTo" />
        </div>
    </div>

    <!-- Summary Badges -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Incomes -->
        <div class="bg-white dark:bg-dark-surface border border-success/30 dark:border-success/20 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-dark-muted">Receitas</p>
                    <p class="text-3xl font-bold text-success dark:text-success mt-1">
                        R$ {{ number_format($totalIncomes, 2, ',', '.') }}
                    </p>
                </div>
                <div class="p-3 bg-success/10 dark:bg-success/20 rounded-full">
                    <svg class="h-8 w-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white dark:bg-dark-surface border border-danger/30 dark:border-danger/20 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-dark-muted">Despesas</p>
                    <p class="text-3xl font-bold text-danger dark:text-danger mt-1">
                        R$ {{ number_format($totalExpenses, 2, ',', '.') }}
                    </p>
                </div>
                <div class="p-3 bg-danger/10 dark:bg-danger/20 rounded-full">
                    <svg class="h-8 w-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Balance -->
        <div class="bg-white dark:bg-dark-surface border border-primary/30 dark:border-primary/20 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-dark-muted">Saldo</p>
                    <p class="text-3xl font-bold {{ $balance >= 0 ? 'text-primary' : 'text-danger' }} dark:{{ $balance >= 0 ? 'text-primary' : 'text-danger' }} mt-1">
                        R$ {{ number_format($balance, 2, ',', '.') }}
                    </p>
                </div>
                <div class="p-3 bg-primary/10 dark:bg-primary/20 rounded-full">
                    <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4 dark:text-dark-text">
            Transações Recentes
        </h2>

        @if ($recentTransactions->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-dark-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-sm text-gray-600 dark:text-dark-muted">
                    Nenhuma transação encontrada no período selecionado.
                </p>
                <p class="text-xs text-gray-500 dark:text-dark-muted mt-1">
                    Registre despesas ou receitas para começar.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-border">
                    <thead class="bg-gray-50 dark:bg-dark-surface-hover/50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-dark-muted uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-dark-muted uppercase tracking-wider">
                                Nome
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-dark-muted uppercase tracking-wider">
                                Categoria
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-dark-muted uppercase tracking-wider">
                                Valor
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-dark-surface divide-y divide-gray-200 dark:divide-dark-border">
                        @foreach ($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-surface-hover/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-dark-muted">
                                    {{ $transaction->expense_date?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-dark-text">
                                    {{ $transaction->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-dark-muted">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-dark-surface-hover/50 text-gray-800 dark:text-dark-muted">
                                        {{ $transaction->category?->name ?? 'Sem categoria' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold">
                                    @if ($transaction->type === 'income')
                                        <span class="text-success">
                                            + R$ {{ number_format($transaction->value, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            - R$ {{ number_format($transaction->value, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($recentTransactions->count() >= 10)
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-dark-muted">
                        Mostrando as 10 transações mais recentes.
                    </p>
                </div>
            @endif
        @endif
    </div>
</div>
