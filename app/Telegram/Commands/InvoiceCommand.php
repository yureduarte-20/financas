<?php

namespace App\Telegram\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Telegram\Bot\Actions;
use Telegram\Bot\FileUpload\InputFile; // <-- Importação necessária
use Telegram\Bot\Commands\Command;

class InvoiceCommand extends LoggedInCommand
{
    protected string $name = 'invoice';
    protected array $aliases = ['extrato'];

    protected string $description = 'Gerar extrato: /extrato {data_inicial} {data_final}';

    // Adicionei ? para tornar os parâmetros realmente opcionais no SDK
    protected string $pattern = '{start_date?\S+} {end_date?\S+}';

    public function executeWithUserLoggedIn()
    {
        // 1. Tratar as Datas Corretamente usando o parseDate
        $rawStartDate = $this->argument('start_date');
        $rawEndDate = $this->argument('end_date');

        $start_date = $rawStartDate ? $this->parseDate($rawStartDate) : now()->startOfMonth()->format('Y-m-d');
        $end_date = $rawEndDate ? $this->parseDate($rawEndDate) : now()->endOfMonth()->format('Y-m-d');

        // 2. Buscar Transações
        $transactions = Transaction::with(['category', 'user'])
            ->whereBetween('expense_date', [
                $start_date,
                $end_date
            ])
            ->where('user_id', $this->getUser()->id)
            ->orderBy('expense_date', 'asc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('value');
        $totalExpense = $transactions->where('type', 'out')->sum('value'); // Alterado para 'out' conforme seu AddExpenseCommand
        $balance = $totalIncome - $totalExpense;

        // 3. Avisar o usuário que um documento está sendo gerado
        $this->replyWithChatAction([
            'action' => Actions::UPLOAD_DOCUMENT, // Melhor que TYPING para arquivos
        ]);

        // 4. Salvar o PDF temporariamente
        $fileName = 'extrato-' . $start_date . '-a-' . $end_date . '.pdf';
        $tempPath = storage_path('app/' . $fileName);

        Pdf::view('pdf.invoice', [
            'transactions' => $transactions,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
        ])
            ->driver('dompdf')
            ->format('a4')
            ->save($tempPath); // Salva no disco temporariamente

        // 5. Enviar o PDF via Telegram
        $dataInicialFormatada = date('d/m/Y', strtotime($start_date));
        $dataFinalFormatada = date('d/m/Y', strtotime($end_date));

        $this->replyWithDocument([
            'document' => InputFile::create($tempPath, $fileName),
            'caption' => "📊 *Extrato Gerado*\nPeríodo: $dataInicialFormatada até $dataFinalFormatada\nSaldo: *R$ " . number_format($balance, 2, ',', '.') . "*",
            'parse_mode' => 'Markdown'
        ]);

        // 6. Limpeza: Apagar o arquivo para não lotar o servidor
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}