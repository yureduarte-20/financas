<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\Actions\Category\DeleteCategoryAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories for the authenticated user.
     */
    #[OA\Get(
        path: "/api/categories",
        summary: "Listar todas as categorias",
        description: "Retorna a lista de categorias que pertencem ao usuário autenticado, ordenadas por nome.",
        tags: ["Categorias"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de categorias.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9c1c9ff5-5858-45bf-97c3-bc7948a4c84d"),
                                new OA\Property(property: "name", type: "string", example: "Alimentação"),
                                new OA\Property(property: "description", type: "string", example: "Supermercado e refeições"),
                                new OA\Property(property: "user_id", type: "string", format: "uuid", example: "e129e061-0b86-4fb4-8975-d91d17983637"),
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
        Gate::authorize('viewAny', Category::class);

        $categories = $request->user()->categories()->orderBy('name')->get();

        return response()->json([
            'data' => $categories,
        ], 200);
    }

    /**
     * Store a newly created category.
     */
    #[OA\Post(
        path: "/api/categories",
        summary: "Criar uma nova categoria",
        description: "Cadastra uma categoria associada ao usuário autenticado.",
        tags: ["Categorias"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Transporte"),
                    new OA\Property(property: "description", type: "string", example: "Combustível, Uber e passagens"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Categoria criada com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Categoria criada com sucesso."),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                            new OA\Property(property: "name", type: "string", example: "Transporte"),
                            new OA\Property(property: "description", type: "string", example: "Combustível, Uber e passagens"),
                            new OA\Property(property: "user_id", type: "string", format: "uuid", example: "e129e061-0b86-4fb4-8975-d91d17983637"),
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
                description: "Erro de validação (ex: nome faltando ou descrição muito curta)."
            )
        ]
    )]
    public function store(Request $request, CreateCategoryAction $action): JsonResponse
    {
        $category = $action->execute($request->all());

        return response()->json([
            'message' => 'Categoria criada com sucesso.',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified category.
     */
    #[OA\Get(
        path: "/api/categories/{category}",
        summary: "Exibir detalhes de uma categoria",
        description: "Retorna os dados de uma categoria específica. Apenas o proprietário da categoria pode acessá-la.",
        tags: ["Categorias"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                description: "UUID da categoria",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalhes da categoria.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                            new OA\Property(property: "name", type: "string", example: "Transporte"),
                            new OA\Property(property: "description", type: "string", example: "Combustível, Uber e passagens"),
                            new OA\Property(property: "user_id", type: "string", format: "uuid", example: "e129e061-0b86-4fb4-8975-d91d17983637"),
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
                description: "Não autorizado (a categoria pertence a outro usuário)."
            ),
            new OA\Response(
                response: 404,
                description: "Categoria não encontrada."
            )
        ]
    )]
    public function show(Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return response()->json([
            'data' => $category,
        ], 200);
    }

    /**
     * Update the specified category.
     */
    #[OA\Put(
        path: "/api/categories/{category}",
        summary: "Atualizar uma categoria",
        description: "Atualiza os campos de uma categoria existente pertencente ao usuário logado.",
        tags: ["Categorias"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                description: "UUID da categoria",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Transporte e Viagens"),
                    new OA\Property(property: "description", type: "string", example: "Gastos com transporte geral e viagens corporativas"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Categoria atualizada com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Categoria atualizada com sucesso."),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "a9ef52eb-5460-449e-b9ef-dcd41bb0b793"),
                            new OA\Property(property: "name", type: "string", example: "Transporte e Viagens"),
                            new OA\Property(property: "description", type: "string", example: "Gastos com transporte geral e viagens corporativas"),
                            new OA\Property(property: "user_id", type: "string", format: "uuid", example: "e129e061-0b86-4fb4-8975-d91d17983637"),
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
                description: "Não autorizado (a categoria pertence a outro usuário)."
            ),
            new OA\Response(
                response: 404,
                description: "Categoria não encontrada."
            ),
            new OA\Response(
                response: 422,
                description: "Erro de validação nos dados enviados."
            )
        ]
    )]
    public function update(Request $request, Category $category, UpdateCategoryAction $action): JsonResponse
    {
        $input = array_merge($request->all(), ['id' => $category->id]);
        
        $updatedCategory = $action->execute($input);

        return response()->json([
            'message' => 'Categoria atualizada com sucesso.',
            'data' => $updatedCategory,
        ], 200);
    }

    /**
     * Remove the specified category.
     */
    #[OA\Delete(
        path: "/api/categories/{category}",
        summary: "Excluir uma categoria",
        description: "Remove uma categoria pertencente ao usuário logado do sistema.",
        tags: ["Categorias"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                description: "UUID da categoria",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Categoria excluída com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Categoria excluída com sucesso.")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            ),
            new OA\Response(
                response: 403,
                description: "Não autorizado (a categoria pertence a outro usuário)."
            ),
            new OA\Response(
                response: 404,
                description: "Categoria não encontrada."
            )
        ]
    )]
    public function destroy(Category $category, DeleteCategoryAction $action): JsonResponse
    {
        $action->execute(['id' => $category->id]);

        return response()->json([
            'message' => 'Categoria excluída com sucesso.',
        ], 200);
    }
}
