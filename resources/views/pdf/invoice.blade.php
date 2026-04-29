<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: white;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .title {
            color: #2563eb;
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
            margin-top: 5px;
        }
        .period-label {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
            text-align: right;
        }
        .period-value {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-align: right;
        }
        
        .summary-table {
            width: 100%;
            margin-bottom: 40px;
        }
        .summary-box {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-label {
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-blue { color: #2563eb; }

        .details-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .transactions-table th {
            background-color: #f3f4f6;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 12px;
            padding: 12px;
            text-align: left;
        }
        .transactions-table th.right { text-align: right; }
        .transactions-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .tx-name {
            font-weight: bold;
            color: #111827;
            margin: 0;
        }
        .tx-desc {
            color: #6b7280;
            font-size: 12px;
            margin: 4px 0 0 0;
        }
        .badge {
            background-color: #e5e7eb;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">Finanças Pessoais</h1>
                <p class="subtitle">Relatório de Transações</p>
            </td>
            <td style="text-align: right;">
                <p class="period-label">Período</p>
                <p class="period-value">{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</p>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td style="padding-right: 10px;">
                <div class="summary-box">
                    <p class="summary-label">Total Receitas</p>
                    <p class="summary-value text-green">R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
                </div>
            </td>
            <td style="padding: 0 10px;">
                <div class="summary-box">
                    <p class="summary-label">Total Despesas</p>
                    <p class="summary-value text-red">R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
                </div>
            </td>
            <td style="padding-left: 10px;">
                <div class="summary-box">
                    <p class="summary-label">Saldo do Período</p>
                    <p class="summary-value {{ $balance >= 0 ? 'text-blue' : 'text-red' }}">R$ {{ number_format($balance, 2, ',', '.') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <h2 class="details-title">Detalhamento das Transações</h2>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->expense_date->format('d/m/Y') }}</td>
                    <td>
                        <p class="tx-name">{{ $transaction->name }}</p>
                        @if($transaction->description)
                            <p class="tx-desc">{{ $transaction->description }}</p>
                        @endif
                    </td>
                    <td>
                        <span class="badge">
                            {{ $transaction->category ? $transaction->category->name : 'Sem categoria' }}
                        </span>
                    </td>
                    <td>
                        @if($transaction->type === 'income')
                            <span class="text-green" style="font-weight: bold;">Receita</span>
                        @else
                            <span class="text-red" style="font-weight: bold;">Despesa</span>
                        @endif
                    </td>
                    <td class="right {{ $transaction->type === 'income' ? 'text-green' : 'text-red' }}" style="font-weight: bold;">
                        {{ $transaction->type === 'income' ? '+' : '-' }} R$ {{ number_format($transaction->value, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #6b7280;">
                        Nenhuma transação encontrada neste período.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
