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

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories for the authenticated user.
     */
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
    public function destroy(Category $category, DeleteCategoryAction $action): JsonResponse
    {
        $action->execute(['id' => $category->id]);

        return response()->json([
            'message' => 'Categoria excluída com sucesso.',
        ], 200);
    }
}
