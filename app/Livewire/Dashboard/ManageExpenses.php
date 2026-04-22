<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Forms\CreateExpenseTransactionForm;
use App\Livewire\Forms\DeleteTransactionForm;
use App\Livewire\Forms\UpdateExpenseTransactionForm;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageExpenses extends Component
{
    use WithPagination;

    public CreateExpenseTransactionForm $createForm;
    public UpdateExpenseTransactionForm $updateForm;
    public DeleteTransactionForm $deleteForm;

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

    public function createExpense(): void
    {
        $this->createForm->submit();
        $this->createForm->reset();
        $this->createForm->expense_date = now()->toDateString();

        session()->flash('success', 'Despesa criada com sucesso.');
        $this->resetPage();
    }

    public function startEditing(string $id): void
    {
        $transaction = Transaction::query()
            ->where('user_id', Auth::id())
            ->where('type', 'out')
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

    public function updateExpense(): void
    {
        $this->updateForm->submit();
        $this->cancelEditing();

        session()->flash('success', 'Despesa atualizada com sucesso.');
    }

    public function deleteExpense(string $id): void
    {
        $this->deleteForm->id = $id;
        $this->deleteForm->submit();
        $this->deleteForm->reset();

        if ($this->updateForm->id === $id) {
            $this->cancelEditing();
        }

        session()->flash('success', 'Despesa removida com sucesso.');
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

        $query = $this->baseExpensesQuery();
        $summaryQuery = clone $query;

        return view('livewire.dashboard.manage-expenses', [
            'categories' => $categories,
            'expenses' => $query->orderByDesc('expense_date')->latest()->paginate(10),
            'totalFilteredExpenses' => (float) $summaryQuery->sum('value'),
            'expensesCount' => (clone $summaryQuery)->count(),
        ]);
    }

    protected function baseExpensesQuery(): Builder
    {
        return Transaction::query()
            ->with('category')
            ->where('user_id', Auth::id())
            ->where('type', 'out')
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
