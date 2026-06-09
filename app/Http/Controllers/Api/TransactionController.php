<?php

namespace App\Http\Controllers\Api;

use App\Actions\Transaction\CreateExpenseTransactionAction;
use App\Actions\Transaction\CreateIncomeTransactionAction;
use App\Actions\Transaction\DeleteIncomeTransactionAction;
use App\Actions\Transaction\DeleteTransactionAction;
use App\Actions\Transaction\UpdateExpenseTransactionAction;
use App\Actions\Transaction\UpdateIncomeTransactionAction;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions for the authenticated user.
     */
    #[OA\Get(
        path: "/api/transactions",
        summary: "Listar todas as transações",
        description: "Retorna a lista de transações (despesas e receitas) do usuário autenticado, ordenada por data em ordem decrescente.",
        tags: ["Transações"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "type",
                in: "query",
                required: false,
                description: "Filtra por tipo de transação ('out' para despesa, 'income' para receita)",
                schema: new OA\Schema(type: "string", enum: ["out", "income"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de transações.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "e440e061-0b86-4fb4-8975-d91d17983637"),
                                new OA\Property(property: "name", type: "string", example: "Combustível"),
                                new OA\Property(property: "value", type: "string", example: "150.00"),
                                new OA\Property(property: "type", type: "string", example: "out"),
                                new OA\Property(property: "expense_date", type: "string", format: "date-time", example: "2026-06-09T00:00:00.000000Z"),
                                new OA\Property(property: "category", type: "object", properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                                    new OA\Property(property: "name", type: "string", example: "Transporte"),
                                ])
                            ]
                        ))
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Transaction::class);

        $query = $request->user()->transactions()->with('category');

        if ($request->has('type')) {
            $type = $request->query('type');
            if (in_array($type, ['out', 'income'])) {
                $query->where('type', $type);
            }
        }

        $transactions = $query->orderBy('expense_date', 'desc')->get();

        return response()->json([
            'data' => $transactions,
        ], 200);
    }

    /**
     * Store a newly created transaction.
     */
    #[OA\Post(
        path: "/api/transactions",
        summary: "Criar uma nova transação",
        description: "Cadastra uma transação (despesa ou receita) para o usuário autenticado.",
        tags: ["Transações"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "name", "value", "expense_date", "category_id"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["out", "income"], example: "out"),
                    new OA\Property(property: "name", type: "string", example: "Almoço de Negócios"),
                    new OA\Property(property: "value", type: "number", format: "float", example: 45.50),
                    new OA\Property(property: "expense_date", type: "string", format: "date", example: "2026-06-09"),
                    new OA\Property(property: "category_id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                    new OA\Property(property: "description", type: "string", example: "Restaurante Sabor"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Transação criada com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transação criada com sucesso."),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "f550e061-0b86-4fb4-8975-d91d17983637"),
                            new OA\Property(property: "name", type: "string", example: "Almoço de Negócios"),
                            new OA\Property(property: "value", type: "number", format: "float", example: 45.50),
                            new OA\Property(property: "type", type: "string", example: "out"),
                            new OA\Property(property: "expense_date", type: "string", format: "date-time", example: "2026-06-09T00:00:00.000000Z"),
                            new OA\Property(property: "category_id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            ),
            new OA\Response(
                response: 422,
                description: "Erro de validação nos campos informados."
            )
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:out,income',
        ]);

        $type = $request->input('type');

        $action = match ($type) {
            'income' => app(CreateIncomeTransactionAction::class),
            'out' => app(CreateExpenseTransactionAction::class),
        };

        $transaction = $action->execute($request->all());

        return response()->json([
            'message' => 'Transação criada com sucesso.',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Display the specified transaction.
     */
    #[OA\Get(
        path: "/api/transactions/{transaction}",
        summary: "Exibir detalhes de uma transação",
        description: "Retorna os dados detalhados de uma transação específica, incluindo sua categoria correspondente.",
        tags: ["Transações"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                description: "UUID da transação",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalhes da transação.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "f550e061-0b86-4fb4-8975-d91d17983637"),
                            new OA\Property(property: "name", type: "string", example: "Almoço de Negócios"),
                            new OA\Property(property: "value", type: "string", example: "45.50"),
                            new OA\Property(property: "type", type: "string", example: "out"),
                            new OA\Property(property: "expense_date", type: "string", format: "date-time", example: "2026-06-09T00:00:00.000000Z"),
                            new OA\Property(property: "category", type: "object", properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                                new OA\Property(property: "name", type: "string", example: "Transporte"),
                            ])
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            ),
            new OA\Response(
                response: 403,
                description: "Não autorizado (a transação pertence a outro usuário)."
            ),
            new OA\Response(
                response: 404,
                description: "Transação não encontrada."
            )
        ]
    )]
    public function show(Transaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);

        return response()->json([
            'data' => $transaction->load('category'),
        ], 200);
    }

    /**
     * Update the specified transaction.
     */
    #[OA\Put(
        path: "/api/transactions/{transaction}",
        summary: "Atualizar uma transação",
        description: "Atualiza os dados de uma transação específica que pertença ao usuário logado.",
        tags: ["Transações"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                description: "UUID da transação",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "value", "expense_date", "category_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Almoço Executivo"),
                    new OA\Property(property: "value", type: "number", format: "float", example: 120.00),
                    new OA\Property(property: "expense_date", type: "string", format: "date", example: "2026-06-09"),
                    new OA\Property(property: "category_id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                    new OA\Property(property: "description", type: "string", example: "Reunião de Alinhamento com time"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Transação atualizada com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transação atualizada com sucesso."),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "f550e061-0b86-4fb4-8975-d91d17983637"),
                            new OA\Property(property: "name", type: "string", example: "Almoço Executivo"),
                            new OA\Property(property: "value", type: "number", format: "float", example: 120.00),
                            new OA\Property(property: "type", type: "string", example: "out"),
                            new OA\Property(property: "expense_date", type: "string", format: "date-time", example: "2026-06-09T00:00:00.000000Z"),
                            new OA\Property(property: "category_id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            ),
            new OA\Response(
                response: 403,
                description: "Não autorizado (a transação pertence a outro usuário)."
            ),
            new OA\Response(
                response: 404,
                description: "Transação não encontrada."
            ),
            new OA\Response(
                response: 422,
                description: "Erro de validação nos campos informados."
            )
        ]
    )]
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        Gate::authorize('update', $transaction);

        $input = array_merge($request->all(), ['id' => $transaction->id]);

        $action = match ($transaction->type) {
            'income' => app(UpdateIncomeTransactionAction::class),
            'out' => app(UpdateExpenseTransactionAction::class),
        };

        $updatedTransaction = $action->execute($input);

        return response()->json([
            'message' => 'Transação atualizada com sucesso.',
            'data' => $updatedTransaction,
        ], 200);
    }

    /**
     * Remove the specified transaction from storage.
     */
    #[OA\Delete(
        path: "/api/transactions/{transaction}",
        summary: "Excluir uma transação",
        description: "Exclui permanentemente uma transação do usuário logado.",
        tags: ["Transações"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                description: "UUID da transação",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transação excluída com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transação excluída com sucesso.")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            ),
            new OA\Response(
                response: 403,
                description: "Não autorizado (a transação pertence a outro usuário)."
            ),
            new OA\Response(
                response: 404,
                description: "Transação não encontrada."
            )
        ]
    )]
    public function destroy(Transaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);

        $input = ['id' => $transaction->id];

        $action = match ($transaction->type) {
            'income' => app(DeleteIncomeTransactionAction::class),
            'out' => app(DeleteTransactionAction::class),
        };

        $action->execute($input);

        return response()->json([
            'message' => 'Transação excluída com sucesso.',
        ], 200);
    }
}
