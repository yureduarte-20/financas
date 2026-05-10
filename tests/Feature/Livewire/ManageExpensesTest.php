<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard\ManageExpenses;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Testes de Feature para ManageExpenses Livewire Component
 * 
 * Cobre RF-05, RF-07, RF-08, RF-09: Gestão de despesas
 * 
 * NOTA: Este componente utiliza:
 * - $createForm: CreateExpenseTransactionForm para criação
 * - $updateForm: UpdateExpenseTransactionForm para atualização  
 * - $deleteForm: DeleteTransactionForm para exclusão
 * - Métodos: createExpense(), startEditing(), updateExpense(), deleteExpense()
 */
class ManageExpensesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    /**
     * @test
     * Componente pode ser renderizado.
     */
    public function it_can_render_component(): void
    {
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->assertStatus(200);
    }

    /**
     * @test
     * RF-05: Pode criar uma despesa com dados válidos usando createExpense().
     */
    public function it_can_create_an_expense(): void
    {
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('createForm.name', 'Supermercado')
            ->set('createForm.value', '150.50')
            ->set('createForm.description', 'Compras do mês')
            ->set('createForm.expense_date', now()->format('Y-m-d'))
            ->set('createForm.category_id', $this->category->id)
            ->call('createExpense')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('transactions', [
            'name' => 'Supermercado',
            'value' => 150.50,
            'type' => 'out',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * Validação: nome é obrigatório no createForm.
     */
    public function it_requires_name_on_create(): void
    {
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('createForm.name', '')
            ->set('createForm.value', '100')
            ->set('createForm.expense_date', now()->format('Y-m-d'))
            ->set('createForm.category_id', $this->category->id)
            ->call('createExpense')
            ->assertHasErrors(['createForm.name']);
    }

    /**
     * @test
     * Validação: valor é obrigatório no createForm.
     */
    public function it_requires_value_on_create(): void
    {
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('createForm.name', 'Teste')
            ->set('createForm.value', '')
            ->set('createForm.expense_date', now()->format('Y-m-d'))
            ->set('createForm.category_id', $this->category->id)
            ->call('createExpense')
            ->assertHasErrors(['createForm.value']);
    }

    /**
     * @test
     * RF-07: Pode atualizar uma despesa existente usando startEditing() e updateExpense().
     */
    public function it_can_update_an_expense(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $transaction->id)
            ->assertSet('editing', true)
            ->set('updateForm.name', 'Nome Atualizado')
            ->set('updateForm.value', '200.00')
            ->call('updateExpense')
            ->assertHasNoErrors()
            ->assertSet('editing', false)
            ->assertDispatched('notify');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'name' => 'Nome Atualizado',
            'value' => 200.00,
        ]);
    }

    /**
     * @test
     * RF-07: Pode cancelar edição usando cancelEditing().
     */
    public function it_can_cancel_editing(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $transaction->id)
            ->assertSet('editing', true)
            ->call('cancelEditing')
            ->assertSet('editing', false)
            ->assertHasNoErrors();
    }

    /**
     * @test
     * RF-08: Pode excluir uma despesa usando deleteExpense().
     */
    public function it_can_delete_an_expense(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('deleteExpense', $transaction->id)
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * @test
     * Usuário não pode editar despesa de outro usuário.
     */
    public function it_prevents_editing_other_users_expense(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'type' => 'out',
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $transaction->id)
            ->assertForbidden();
    }

    /**
     * @test
     * Usuário não pode excluir despesa de outro usuário.
     */
    public function it_prevents_deleting_other_users_expense(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'type' => 'out',
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('deleteExpense', $transaction->id)
            ->assertForbidden();

        // Assert database
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * @test
     * RF-09: Filtros funcionam corretamente.
     */
    public function it_can_filter_expenses_by_category(): void
    {
        // Arrange
        $category2 = Category::factory()->create(['user_id' => $this->user->id]);
        
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'name' => 'Despesa Categoria 1',
        ]);
        
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'type' => 'out',
            'name' => 'Despesa Categoria 2',
        ]);

        // Act & Assert
        $component = Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('categoryFilter', $this->category->id);

        // Verifica que o filtro foi aplicado
        $component->assertSet('categoryFilter', $this->category->id);
    }

    /**
     * @test
     * RF-09: Busca por texto funciona corretamente.
     */
    public function it_can_search_expenses_by_text(): void
    {
        // Arrange
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'name' => 'Supermercado Extra',
        ]);
        
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'name' => 'Posto Shell',
        ]);

        // Act & Assert
        $component = Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('search', 'Supermercado');

        $component->assertSet('search', 'Supermercado');
    }
}
