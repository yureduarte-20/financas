<?php

namespace App\Actions\Category;

use App\Actions\AbstractAction;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CreateCategoryAction extends AbstractAction
{
    /**
     * Create a new class instance.
     */

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|min:3'
        ];
    }
    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);
        $validated['user_id'] = Auth::user()->id;
        Gate::authorize('create', Category::class);
        return Category::create($validated);
    }
}
