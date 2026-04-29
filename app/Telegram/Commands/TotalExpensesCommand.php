<?php

namespace App\Telegram\Commands;

use App\Models\Transaction;
use Carbon\Carbon;



class TotalExpensesCommand extends LoggedInCommand
{
    protected string $name = 'expenses';
    protected $alias = ['total'];
    protected string $description = 'Calcular todas as dispesas';
    protected string $pattern = '{dataInicial:[\d\/\-]*}\s*{dataFinal:[\d\/\-]*}';

    public function executeWithUserLoggedIn()
    {
        $user = $this->getUser();

        // Captura e formata as datas
        $inicioRaw = $this->argument('dataInicial');
        $fimRaw = $this->argument('dataFinal');

        $dataInicial = $this->parseDate($inicioRaw);
        $dataFinal = $this->parseDate($fimRaw);

        $query = Transaction::where('user_id', $user->id)
            ->where('type', 'out');

        // Aplica os filtros condicionalmente
        if ($dataInicial) {
            $query->whereDate('date', '>=', $dataInicial);
        }

        if ($dataFinal) {
            $query->whereDate('date', '<=', $dataFinal);
        }

        $amount = $query->sum('value');

        $periodoTexto = $this->getPeriodoTexto($dataInicial, $dataFinal);

        return $this->replyWithMessage([
            'text' => "💰 *Resumo de Gastos*\n" .
                "Período: $periodoTexto\n" .
                "Total: *R$ " . number_format($amount, 2, ',', '.') . "*"
        ]);
    }
    /**
     * Converte datas de DD/MM/AAAA para YYYY-MM-DD
     */
    private function parseDate($date)
    {
        if (!$date)
            return null;

        try {
            // Normaliza o separador e converte para o formato SQL
            return Carbon::parse(str_replace('/', '-', $date))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getPeriodoTexto($inicio, $fim)
    {
        if ($inicio && $fim)
            return "de " . date('d/m/Y', strtotime($inicio)) . " até " . date('d/m/Y', strtotime($fim));
        if ($inicio)
            return "desde " . date('d/m/Y', strtotime($inicio));
        if ($fim)
            return "até " . date('d/m/Y', strtotime($fim));
        return "todo o histórico";
    }
}
