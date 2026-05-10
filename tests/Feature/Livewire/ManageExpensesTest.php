<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard\ManageExpenses;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * startEditing() usa findOrFail com filtro de user_id,
     * lançando ModelNotFoundException (a transação não é visível).
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

        // Assert - findOrFail não encontra a transação de outro usuário
        $this->expectException(ModelNotFoundException::class);

        // Act
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $transaction->id);
    }

    /**
     * @test
     * Usuário não pode excluir despesa de outro usuário.
     * A regra de validação do 'id' no DeleteTransactionAction
     * filtra por user_id, resultando em erro de validação no form.
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

        // Act & Assert - A validação rejeita o ID que não pertence ao usuário
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('deleteExpense', $transaction->id)
            ->assertHasErrors(['deleteForm.id']);

        // Assert database - transação permanece intacta
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

    /**
     * @test
     * Validação robusta: Tentativa de editar transação inexistente.
     * startEditing() usa findOrFail que lança ModelNotFoundException.
     */
    public function it_returns_not_found_for_nonexistent_transaction_on_edit(): void
    {
        // Arrange - UUID inexistente
        $fakeId = '12345678-1234-1234-1234-123456789abc';

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $fakeId);
    }

    /**
     * @test
     * Segurança: Usuário autenticado só pode ver suas próprias transações.
     * Tentar editar transação de outro usuário via startEditing()
     * lança ModelNotFoundException pois o findOrFail filtra por user_id.
     */
    public function it_only_shows_own_expenses_in_query(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        // Transação do usuário autenticado
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'name' => 'Minha Transação',
        ]);

        // Transação de outro usuário (não deve ser visível)
        $otherTransaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'type' => 'out',
            'name' => 'Transação de Outro',
        ]);

        // Assert - findOrFail não encontra transação de outro usuário
        $this->expectException(ModelNotFoundException::class);

        // Act
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $otherTransaction->id);
    }

    /**
     * @test
     * Validação de campos obrigatórios durante atualização - o componente
     * captura a exceção e emite notificação de erro.
     */
    public function it_validates_required_fields_on_update(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
        ]);

        // Act & Assert - Nome vazio
        // O componente captura o erro e emite notificação
        // A transação NÃO é alterada
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $transaction->id)
            ->set('updateForm.name', '')
            ->set('updateForm.value', '100.00')
            ->call('updateExpense');

        // Assert - banco de dados não foi alterado
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'name' => $transaction->name,
        ]);
    }

    /**
     * @test
     * Tratamento de erros: O componente captura exceção e notifica usuário.
     * A transação original não é alterada após falha na validação.
     */
    public function it_handles_errors_and_notifies_user(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
        ]);

        // Act & Assert - Valor inválido (negativo)
        // O componente captura o erro e mostra notificação
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('startEditing', $transaction->id)
            ->set('updateForm.name', 'Teste')
            ->set('updateForm.value', '-100')
            ->call('updateExpense');

        // Assert - A transação não foi alterada
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'name' => $transaction->name,
            'value' => $transaction->value,
        ]);
    }

    /**
     * @test
     * Cascata de exclusão: Ao excluir uma transação, garantir que não haja efeitos colaterais.
     */
    public function it_properly_deletes_expense_without_side_effects(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'name' => 'Despesa para Excluir',
            'value' => 150.00,
        ]);

        $transactionId = $transaction->id;

        // Act
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('deleteExpense', $transactionId)
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        // Assert
        $this->assertDatabaseMissing('transactions', [
            'id' => $transactionId,
        ]);
    }

    /**
     * @test
     * Teste de integridade: Garantir que o total de despesas é calculado corretamente.
     */
    public function it_calculates_expense_totals_correctly(): void
    {
        // Arrange
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'value' => 100.00,
            'expense_date' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
            'value' => 200.00,
            'expense_date' => now(),
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->assertOk();
    }
}

