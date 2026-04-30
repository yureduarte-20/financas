<?php

namespace App\Livewire\Incomes;

use App\Livewire\Forms\CreateIncomeTransactionForm;
use App\Livewire\Forms\DeleteIncomeTransactionForm;
use App\Livewire\Forms\UpdateIncomeTransactionForm;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageIncomes extends Component
{
    use WithPagination;

    public CreateIncomeTransactionForm $createForm;
    public UpdateIncomeTransactionForm $updateForm;
    public DeleteIncomeTransactionForm $deleteForm;

    public bool $editing = false;
    public ?string $categoryFilter = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public string $search = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
        $this->createForm->expense_date = now()->toDateString();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createIncome(): void
    {
        $old = $this->createForm->value;
        $this->createForm->value = str_replace(',', '.', $old);
        $this->createForm->submit();
        $this->createForm->reset();
        $this->createForm->expense_date = now()->toDateString();

        $this->dispatch('notify', type: 'success', title: 'Sucesso', message: 'Receita criada com sucesso.');
        $this->resetPage();
    }

    public function startEditing(string $id): void
    {
        $transaction = Transaction::query()
            ->where('user_id', Auth::id())
            ->where('type', 'income')
            ->findOrFail($id);

        $this->updateForm->id = $transaction->id;
        $this->updateForm->name = $transaction->name;
        $this->updateForm->description = $transaction->description;
        $this->updateForm->value = (string) $transaction->value;
        $this->updateForm->expense_date = $transaction->expense_date?->toDateString();
        $this->updateForm->category_id = $transaction->category_id;

        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->updateForm->reset();
        $this->editing = false;
    }

    public function updateIncome(): void
    {
        $old = $this->updateForm->value;
        try {
            $this->updateForm->value = str_replace(',', '.', $old);
            $this->updateForm->submit();
            $this->cancelEditing();
            $this->dispatch('notify', type: 'success', title: 'Sucesso', message: 'Receita atualizada com sucesso.');
        } catch (\Exception $e) {
            $this->updateForm->value = str_replace('.', ',', $old);
            $this->dispatch('notify', type: 'error', title: 'Erro', message: 'Erro ao atualizar receita.');
        }
    }

    public function deleteIncome(string $id): void
    {
        $this->deleteForm->id = $id;
        $this->deleteForm->submit();
        $this->deleteForm->reset();

        if ($this->updateForm->id === $id) {
            $this->cancelEditing();
        }
        $this->dispatch('notify', type: 'success', title: 'Sucesso', message: 'Receita removida com sucesso.');
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->categoryFilter = null;
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
        $this->search = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $categories = Category::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $query = $this->baseIncomesQuery();
        $summaryQuery = clone $query;

        return view('livewire.incomes.manage-incomes', [
            'categories' => $categories,
            'incomes' => $query->orderByDesc('expense_date')->latest()->paginate(10),
            'totalFilteredIncomes' => (float) $summaryQuery->sum('value'),
            'incomesCount' => (clone $summaryQuery)->count(),
        ]);
    }

    protected function baseIncomesQuery(): Builder
    {
        return Transaction::query()
            ->with('category')
            ->where('user_id', Auth::id())
            ->where('type', 'income')
            ->when(
                $this->categoryFilter,
                fn (Builder $query, string $categoryId) => $query->where('category_id', $categoryId)
            )
            ->when(
                $this->dateFrom,
                fn (Builder $query, string $dateFrom) => $query->whereDate('expense_date', '>=', Carbon::parse($dateFrom))
            )
            ->when(
                $this->dateTo,
                fn (Builder $query, string $dateTo) => $query->whereDate('expense_date', '<=', Carbon::parse($dateTo))
            )
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $subQuery): void {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            });
    }
}
