<?php

namespace App\Telegram\Commands;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AddExpenseCommand extends LoggedInCommand
{
    protected string $name = 'add_expense';
    protected array $aliases = ['gasto'];
    protected string $description = 'Registrar gasto: /gasto {valor} {descrição} {categoria} {data}';

    // Simplificamos o pattern. O SDK separa por espaços automaticamente. 
    // Usamos interrogação (?) para tornar os campos seguintes opcionais.
    protected string $pattern = "{valor} {description?} {category?} {data?}";

    public function executeWithUserLoggedIn()
    {
        $user = $this->getUser();
        $rawValor = $this->argument('valor');

        if (!$rawValor) {
            return $this->replyWithMessage(['text' => '⚠️ Informe ao menos o valor. Ex: /gasto 15,50']);
        }

        Log::info('AddExpenseCommand - Argumentos Recebidos', $this->arguments);

        // 1. Sanitize e Validação do Valor
        $valor = str_replace(['R$', ' '], '', $rawValor);
        $valor = str_replace(',', '.', $valor);

        if (!is_numeric($valor)) {
            return $this->replyWithMessage(['text' => '❌ Valor inválido. Use o formato: 100,00']);
        }

        // 2. Tratamento de Categoria (Ajustado para 'category' para bater com o pattern)
        $categoryName = $this->argument('category', 'Outros');
        $categoryId = $this->findCategoryByName($categoryName)->id;

        // 3. Tratamento de Data
        // Se o parse falhar (ex: usuário digitou "Outros" no lugar da data), usa o now()
        $dataRaw = $this->argument('data');
        $dataParsed = $this->parseDate($dataRaw);
        $expenseDate = $dataParsed ?: now()->format('Y-m-d');

        // 4. Execução
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'type' => 'out',
            'value' => $valor,
            'name' => $this->argument('description', 'Transação via Telegram'),
            'description' => $this->argument('description', 'Telegram: Sem descrição'),
            'expense_date' => $expenseDate,
            'status' => 'published',
        ]);

        return $this->replyWithMessage([
            'text' => '✅ Despesa de R$ ' . number_format((float) $valor, 2, ',', '.') . ' salva com sucesso!'
        ]);
    }

    private function findCategoryByName(string $name)
    {
        return Category::firstOrCreate([
            'user_id' => $this->getUser()->id,
            'name' => ucfirst(strtolower($name)) // Mantém o banco organizado (Ex: trufa -> Trufa)
        ]);
    }

    private function parseDate($date)
    {
        if (!$date)
            return null;

        try {
            // Tenta criar pelo formato brasileiro
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            // Se o usuário digitou texto em vez de data, retorna null
            return null;
        }
    }
}