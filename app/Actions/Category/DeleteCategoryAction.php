<?php

namespace App\Actions\Category;

use App\Actions\AbstractAction;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;

class DeleteCategoryAction extends AbstractAction
{
    /**
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|uuid|exists:categories,id',
        ];
    }

    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);
        $category = Category::findOrFail($validated['id']);

        Gate::authorize('delete', $category);

        return $category->delete();
    }
}
