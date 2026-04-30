<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $transactions = Transaction::with(['category', 'user'])
            ->whereBetween('expense_date', [
                $request->start_date,
                $request->end_date
            ])
            ->orderBy('expense_date', 'asc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('value');
        $totalExpense = $transactions->where('type', 'expense')->sum('value');
        $balance = $totalIncome - $totalExpense;

        return Pdf::view('pdf.invoice', [
            'transactions' => $transactions,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
        ])
            ->driver('dompdf')
            ->format('a4')
            ->name('invoice-' . $request->start_date . '-to-' . $request->end_date . '.pdf')
            ->download();
    }
}
