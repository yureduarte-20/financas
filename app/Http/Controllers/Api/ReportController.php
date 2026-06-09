<?php

namespace App\Http\Controllers\Api;

use App\Actions\Report\GenerateReportAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    /**
     * Generate financial report.
     *
     * @param Request $request
     * @param GenerateReportAction $action
     * @return JsonResponse
     */
    #[OA\Get(
        path: "/api/reports",
        summary: "Gerar relatório financeiro",
        description: "Compila o resumo financeiro (totais de receitas, despesas, saldo), o detalhamento por categorias e a lista de transações com base em filtros opcionais de data, tipo e categoria.",
        tags: ["Relatórios"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "start_date",
                in: "query",
                required: false,
                description: "Filtra transações a partir desta data (formato YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-06-01")
            ),
            new OA\Parameter(
                name: "end_date",
                in: "query",
                required: false,
                description: "Filtra transações até esta data (formato YYYY-MM-DD). Deve ser igual ou posterior à data de início.",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-06-30")
            ),
            new OA\Parameter(
                name: "category_id",
                in: "query",
                required: false,
                description: "Filtra apenas transações da categoria informada. Deve pertencer ao usuário logado.",
                schema: new OA\Schema(type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793")
            ),
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
                description: "Relatório consolidado gerado com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "summary", type: "object", properties: [
                                new OA\Property(property: "total_income", type: "number", format: "float", example: 3500.00),
                                new OA\Property(property: "total_expense", type: "number", format: "float", example: 1200.00),
                                new OA\Property(property: "balance", type: "number", format: "float", example: 2300.00),
                            ]),
                            new OA\Property(property: "breakdown", type: "array", items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "category_id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                                    new OA\Property(property: "category_name", type: "string", example: "Transporte"),
                                    new OA\Property(property: "total", type: "number", format: "float", example: 1200.00),
                                    new OA\Property(property: "count", type: "integer", example: 8),
                                ]
                            )),
                            new OA\Property(property: "transactions", type: "array", items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "e440e061-0b86-4fb4-8975-d91d17983637"),
                                    new OA\Property(property: "name", type: "string", example: "Aluguel"),
                                    new OA\Property(property: "value", type: "string", example: "1200.00"),
                                    new OA\Property(property: "type", type: "string", example: "out"),
                                    new OA\Property(property: "expense_date", type: "string", format: "date-time", example: "2026-06-05T00:00:00.000000Z"),
                                    new OA\Property(property: "category_id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                                ]
                            ))
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
                description: "Parâmetros inválidos (ex: data de término anterior à de início, UUID de categoria inválido)."
            )
        ]
    )]
    public function __invoke(Request $request, GenerateReportAction $action): JsonResponse
    {
        $reportData = $action->execute($request->query());

        return response()->json([
            'data' => $reportData,
        ], 200);
    }
}
