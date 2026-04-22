<?php

namespace App\Livewire\Categories;

use App\Livewire\Forms\CreateCategoryForm;
use App\Livewire\Forms\DeleteCategoryForm;
use App\Livewire\Forms\UpdateCategoryForm;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManageCategories extends Component
{
    public CreateCategoryForm $createForm;
    public UpdateCategoryForm $updateForm;
    public DeleteCategoryForm $deleteForm;

    public bool $editing = false;

    public function render()
    {
        return view('livewire.categories.manage-categories', [
            'categories' => Category::query()
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),
        ]);
    }

    public function createCategory(): void
    {
        $this->createForm->submit();
        $this->createForm->reset();

        session()->flash('success', 'Categoria criada com sucesso.');
    }

    public function startEditing(string $id): void
    {
        $category = Category::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->updateForm->id = $category->id;
        $this->updateForm->name = $category->name;
        $this->updateForm->description = $category->description;
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->updateForm->reset();
        $this->editing = false;
    }

    public function updateCategory(): void
    {
        $this->updateForm->submit();
        $this->cancelEditing();

        session()->flash('success', 'Categoria atualizada com sucesso.');
    }

    public function deleteCategory(string $id): void
    {
        $this->deleteForm->id = $id;
        $this->deleteForm->submit();
        $this->deleteForm->reset();

        if ($this->updateForm->id === $id) {
            $this->cancelEditing();
        }

        session()->flash('success', 'Categoria removida com sucesso.');
    }
}
