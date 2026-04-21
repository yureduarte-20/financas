<?php

namespace App\Actions\Category;

use App\Actions\AbstractAction;
use App\Models\Category;
use Gate;

class UpdateCategoryAction extends AbstractAction
{
    public function __construct(
        private Category $category
    ) {
    }
    /**
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|uuid|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|min:3'
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): mixed
    {
        $category = Category::findOrFail($input['id']);
        Gate::authorize('update', $category);
        return $category->update($input);
    }
}
