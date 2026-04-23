<div>
    <x-errors class="mb-4" />

    <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4 mb-6">
        <h2 class="text-lg font-semibold mb-2 dark:text-dark-text">
            Importar documento com IA
        </h2>
        <p class="text-sm text-gray-600 dark:text-dark-muted mb-4">
            Envie um PDF ou imagem (JPG/PNG) de recibo ou fatura. A IA extrai os dados para você revisar e confirmar antes de gravar as despesas.
        </p>

        <form wire:submit="analyze" class="space-y-4">
            @if(!$file)
            <x-input
                label="Arquivo"
                type="file"
                wire:model="file"
                accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg"
            />
            @endif
            <div class="flex flex-wrap gap-2">
                <x-button type="submit" color="primary" wire:loading.attr="disabled" wire:target="analyze,file">
                    <span wire:loading.remove wire:target="analyze">Analisar com IA</span>
                    <span wire:loading wire:target="analyze">Analisando...</span>
                </x-button>

                @if ($documentId || $file)
                    <x-button.outline type="button" color="dark" wire:click="resetImport" wire:loading.attr="disabled" wire:target="analyze">
                        Limpar
                    </x-button.outline>
                @endif
            </div>
        </form>
    </div>

    @if ($documentId)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
                <h3 class="text-md font-semibold mb-3 dark:text-dark-text">Resumo extraído</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600 dark:text-dark-muted">Estabelecimento</dt>
                        <dd class="text-gray-900 dark:text-dark-text text-right">{{ $establishment ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600 dark:text-dark-muted">Data do documento</dt>
                        <dd class="text-gray-900 dark:text-dark-text text-right">{{ $documentDate ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600 dark:text-dark-muted">Valor total (IA)</dt>
                        <dd class="text-gray-900 dark:text-dark-text text-right">
                            @if ($documentTotal !== null)
                                R$ {{ number_format($documentTotal, 2, ',', '.') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600 dark:text-dark-muted">Categoria sugerida</dt>
                        <dd class="text-gray-900 dark:text-dark-text text-right">{{ $suggestedCategoryLabel ?? '—' }}</dd>
                    </div>
                </dl>

                @if (! empty($missingFields))
                    <x-alert type="warning" title="Campos com baixa confiança" class="mt-4">
                        {{ implode(', ', $missingFields) }}
                    </x-alert>
                @endif
            </div>

            <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
                <h3 class="text-md font-semibold mb-3 dark:text-dark-text">Pré-visualização do arquivo</h3>
                @php
                    $ext = strtolower(pathinfo($establishment ?? '', PATHINFO_EXTENSION));
                @endphp
                @if (in_array($storedExtension, ['jpg', 'jpeg', 'png'], true))
                    <img
                        src="{{ route('documents.file', ['document' => $documentId]) }}"
                        alt="Pré-visualização do documento importado"
                        class="max-h-72 w-auto rounded-md border border-surface-200 dark:border-dark-border"
                        loading="lazy"
                    />
                @elseif ($storedExtension === 'pdf')
                    <iframe
                        src="{{ route('documents.file', ['document' => $documentId]) }}"
                        class="w-full min-h-72 rounded-md border border-surface-200 dark:border-dark-border"
                        title="Pré-visualização PDF"
                    ></iframe>
                @else
                    <p class="text-sm text-gray-600 dark:text-dark-muted">Pré-visualização indisponível para este tipo de arquivo.</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-dark-surface border border-surface-200 dark:border-dark-border rounded-lg p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-md font-semibold dark:text-dark-text">Transações para aprovação</h3>
                <x-button type="button" color="success" wire:click="confirmImport" wire:loading.attr="disabled" wire:target="confirmImport">
                    <span wire:loading.remove wire:target="confirmImport">Confirmar importação</span>
                    <span wire:loading wire:target="confirmImport">Salvando...</span>
                </x-button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 dark:text-dark-muted border-b border-surface-200 dark:border-dark-border">
                            <th class="py-2 pr-3 font-medium">Incluir</th>
                            <th class="py-2 pr-3 font-medium">Nome</th>
                            <th class="py-2 pr-3 font-medium">Descrição</th>
                            <th class="py-2 pr-3 font-medium">Valor (R$)</th>
                            <th class="py-2 pr-3 font-medium">Data</th>
                            <th class="py-2 font-medium">Categoria</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewRows as $index => $row)
                            <tr wire:key="preview-row-{{ $index }}" class="border-b border-surface-100 dark:border-dark-border align-top">
                                <td class="py-3 pr-3">
                                    <x-switch wire:model.live.debounce="previewRows.{{ $index }}.include" />
                                </td>
                                <td class="py-3 pr-3 min-w-[10rem]">
                                    <x-input wire:model.live.debounce="previewRows.{{ $index }}.name" />
                                </td>
                                <td class="py-3 pr-3 min-w-[10rem]">
                                    <x-input wire:model.live.debounce="previewRows.{{ $index }}.description" />
                                </td>
                                <td class="py-3 pr-3 min-w-[8rem]">
                                    <x-input wire:model.live.debounce="previewRows.{{ $index }}.value" />
                                </td>
                                <td class="py-3 pr-3 min-w-[9rem]">
                                    <x-input type="date" wire:model.live.debounce="previewRows.{{ $index }}.expense_date" />
                                </td>
                                <td class="py-3 min-w-[12rem]">
                                    <x-select.native wire:model.live.debounce="previewRows.{{ $index }}.category_id">
                                        <option value="">Selecione</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </x-select.native>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
