<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardSummary extends Component
{
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
    }

    public function render(): View
    {
        $userId = Auth::id();

        $query = Transaction::query()->where('user_id', $userId);

        $totalExpenses = (clone $query)
            ->where('type', 'out')
            ->whereBetween('expense_date', [$this->dateFrom, $this->dateTo])
            ->sum('value');

        $totalIncomes = (clone $query)
            ->where('type', 'income')
            ->whereBetween('expense_date', [$this->dateFrom, $this->dateTo])
            ->sum('value');

        $balance = $totalIncomes - $totalExpenses;

        $recentTransactions = (clone $query)
            ->with('category')
            ->whereBetween('expense_date', [$this->dateFrom, $this->dateTo])
            ->orderByDesc('expense_date')
            ->latest()
            ->limit(10)
            ->get();

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return view('livewire.dashboard.dashboard-summary', [
            'totalExpenses' => (float) $totalExpenses,
            'totalIncomes' => (float) $totalIncomes,
            'balance' => (float) $balance,
            'recentTransactions' => $recentTransactions,
            'categories' => $categories,
        ]);
    }
}
