<?php

namespace App\Livewire\Dashboard;

use App\Actions\Transaction\CreateTransactionAction;
use App\Models\Category;
use App\Models\Document;
use App\Service\AiService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ImportDocument extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public bool $analyzing = false;

    public ?string $documentId = null;

    public ?string $storedExtension = null;

    /** @var list<string> */
    public array $missingFields = [];

    public ?string $establishment = null;

    public ?string $documentDate = null;

    public ?float $documentTotal = null;

    public ?string $suggestedCategoryLabel = null;

    /**
     * Linhas para aprovação: include, name, description, value, expense_date, category_id
     *
     * @var list<array{include: bool, name: string, description: ?string, value: string, expense_date: string, category_id: ?string}>
     */
    public array $previewRows = [];

    public function analyze(AiService $aiService): void
    {
        $this->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);


        $this->analyzing = true;
        $this->resetPreviewState();

        try {
            $userId = Auth::id();
            $mime = $this->file->getMimeType() ?? 'application/octet-stream';
            $ext = $this->resolveExtension($mime);

            $document = Document::query()->create([
                'name' => $this->file->getClientOriginalName(),
                'sha1' => hash_file('sha1', $this->file->getRealPath()) ?: '',
                'size' => (int) $this->file->getSize(),
                'user_id' => $userId,
            ]);

            $relativePath = 'documents/' . $userId . '/' . $document->id . '.' . $ext;
            Storage::disk('local')->put($relativePath, file_get_contents($this->file->getRealPath()) ?: '');

            $absolutePath = Storage::disk('local')->path($relativePath);
            $analysis = $aiService->analyzeExpenseDocument($absolutePath, $mime);

            $this->documentId = $document->id;
            $this->storedExtension = $ext;
            $this->missingFields = $analysis['campos_nao_identificados'];
            $this->establishment = $analysis['estabelecimento'];
            $this->documentDate = $analysis['data_documento'];
            $this->documentTotal = $analysis['valor_total'];
            $this->suggestedCategoryLabel = $analysis['categoria_sugerida'];

            $categories = $this->categoriesQuery()->get();
            $defaultCategoryId = $this->resolveCategoryId($analysis['categoria_sugerida'], $categories);

            $this->previewRows = $this->buildPreviewRows($analysis, $defaultCategoryId);

            $this->dispatch('notify', type: 'success', title: 'Análise concluída', message: 'Revise as linhas na tabela e confirme para gravar as despesas.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'danger', title: 'Erro na análise', message: $e->getMessage());
        } finally {
            $this->analyzing = false;
        }
    }

    public function confirmImport(CreateTransactionAction $createTransaction): void
    {
        if (! $this->documentId) {
            $this->dispatch('notify', type: 'warning', title: 'Atenção', message: 'Envie e analise um documento antes de confirmar.');

            return;
        }

        Gate::authorize('create', \App\Models\Transaction::class);

        $rules = [
            'previewRows' => 'required|array|min:1',
            'previewRows.*.include' => 'boolean',
            'previewRows.*.name' => 'required|string|max:255',
            'previewRows.*.description' => 'nullable|string|max:255',
            'previewRows.*.value' => 'required|string',
            'previewRows.*.expense_date' => 'required|date',
            'previewRows.*.category_id' => 'required|uuid|exists:categories,id',
        ];

        $this->validate($rules, [], [
            'previewRows.*.name' => 'nome',
            'previewRows.*.value' => 'valor',
            'previewRows.*.expense_date' => 'data',
            'previewRows.*.category_id' => 'categoria',
        ]);

        $selected = collect($this->previewRows)->filter(fn (array $row): bool => $row['include'] ?? false);
        if ($selected->isEmpty()) {
            $this->dispatch('notify', type: 'warning', title: 'Atenção', message: 'Marque ao menos uma linha para importar.');

            return;
        }

        $userId = Auth::id();
        foreach ($selected as $row) {
            $category = Category::query()
                ->where('user_id', $userId)
                ->whereKey($row['category_id'])
                ->first();
            if (! $category) {
                $this->addError('previewRows.0.category_id', 'Categoria inválida para esta conta.');

                return;
            }
        }


        $created = 0;
        DB::transaction(function () use ($selected, $createTransaction, &$created, $userId): void {
            foreach ($selected as $row) {
                $value = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,.-]/', '', (string) $row['value']) ?? '0');
                if ($value <= 0) {
                    continue;
                }
                $createTransaction->execute([
                    'name' => $row['name'],
                    'description' => $row['description'] ?: null,
                    'value' => $value,
                    'expense_date' => $row['expense_date'],
                    'category_id' => $row['category_id'],
                    'document_id' => $this->documentId,
                ]);
                $created++;
            }
        });

        if ($created === 0) {
            $this->dispatch('notify', type: 'warning', title: 'Nada importado', message: 'Verifique os valores das linhas selecionadas.');

            return;
        }

        $this->dispatch('notify', type: 'success', title: 'Importação concluída', message: $created === 1 ? 'Uma despesa foi registrada.' : "{$created} despesas foram registradas.");
        $this->resetImport();
    }

    public function resetImport(): void
    {
        $this->file = null;
        $this->resetPreviewState();
    }

    public function render(): View
    {
        return view('livewire.dashboard.import-document', [
            'categories' => $this->categoriesQuery()->orderBy('name')->get(),
        ]);
    }

    protected function categoriesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Category::query()->where('user_id', Auth::id());
    }

    private function resetPreviewState(): void
    {
        $this->documentId = null;
        $this->storedExtension = null;
        $this->missingFields = [];
        $this->establishment = null;
        $this->documentDate = null;
        $this->documentTotal = null;
        $this->suggestedCategoryLabel = null;
        $this->previewRows = [];
    }

    private function resolveExtension(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'bin',
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     */
    private function resolveCategoryId(?string $suggested, $categories): ?string
    {
        if (! $suggested || $categories->isEmpty()) {
            return $categories->first()?->id;
        }

        $needle = Str::lower(Str::ascii(trim($suggested)));

        $exact = $categories->first(fn (Category $c): bool => Str::lower(Str::ascii(trim($c->name))) === $needle);
        if ($exact) {
            return $exact->id;
        }

        $partial = $categories->first(function (Category $c) use ($needle): bool {
            $hay = Str::lower(Str::ascii(trim($c->name)));

            return str_contains($hay, $needle) || str_contains($needle, $hay);
        });
        if ($partial) {
            return $partial->id;
        }

        return $categories->first()?->id;
    }

    /**
     * @param  array{
     *     estabelecimento: ?string,
     *     data_documento: ?string,
     *     valor_total: ?float,
     *     categoria_sugerida: ?string,
     *     itens: list<array{descricao: string, quantidade: float|int, valor: float, data: ?string}>,
     *     campos_nao_identificados: list<string>
     * }  $analysis
     * @return list<array{include: bool, name: string, description: ?string, value: string, expense_date: string, category_id: ?string}>
     */
    private function buildPreviewRows(array $analysis, ?string $defaultCategoryId): array
    {
        $categories = $this->categoriesQuery()->get();
        $rows = [];
        $establishment = $analysis['estabelecimento'] ?? 'Despesa importada';
        $fallbackDate = $analysis['data_documento'] && $this->isValidDate($analysis['data_documento'])
            ? $analysis['data_documento']
            : now()->toDateString();

        if (count($analysis['itens']) > 0) {
            foreach ($analysis['itens'] as $item) {
                $date = $item['data'] && $this->isValidDate($item['data']) ? $item['data'] : $fallbackDate;
                $rows[] = [
                    'include' => true,
                    'name' => Str::limit(trim($item['descricao']) !== '' ? $item['descricao'] : $establishment, 255),
                    'description' => '',
                    'value' => number_format((float) $item['valor'], 2, ',', '.'),
                    'expense_date' => $date,
                    'category_id' => $defaultCategoryId,
                ];
            }
            // Fix description for first row - simplify
            foreach ($rows as $i => $row) {
                $rows[$i]['description'] = 'Qtd: ' . ($analysis['itens'][$i]['quantidade'] ?? 1);
            }
        } else {
            $total = $analysis['valor_total'] ?? 0.0;
            $rows[] = [
                'include' => true,
                'name' => Str::limit($establishment, 255),
                'description' => null,
                'value' => number_format((float) $total, 2, ',', '.'),
                'expense_date' => $fallbackDate,
                'category_id' => $defaultCategoryId,
            ];
        }

        foreach ($rows as $i => $row) {
            if ($row['category_id'] === null) {
                $rows[$i]['category_id'] = $this->resolveCategoryId($analysis['categoria_sugerida'], $categories);
            }
        }

        return $rows;
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }
}
