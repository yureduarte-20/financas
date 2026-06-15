<?php

namespace App\Actions\Category;

use App\Actions\AbstractAction;
use App\Models\Category;
use Gate;

class UpdateCategoryAction extends AbstractAction
{
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
        $validated = $this->validate($input);
        $category = Category::findOrFail($validated['id']);
        Gate::authorize('update', $category);
        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return $category->refresh();
    }
}
