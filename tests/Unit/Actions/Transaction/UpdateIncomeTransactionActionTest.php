<?php

namespace Tests\Unit\Actions\Transaction;

use App\Actions\Transaction\UpdateIncomeTransactionAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Testes Unitários para UpdateIncomeTransactionAction
 * 
 * Cobre RF-07: Atualização de receitas com validação de permissões
 */
class UpdateIncomeTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateIncomeTransactionAction $action;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateIncomeTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        Auth::login($this->user);
    }

    /**
     * @test
     * RF-07: Usuário pode atualizar sua própria receita.
     */
    public function it_allows_user_to_update_own_income(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'name' => 'Salário Original',
            'value' => 5000.00,
        ]);

        $input = [
            'id' => $transaction->id,
            'name' => 'Salário Atualizado',
            'description' => 'Salário de maio',
            'value' => 5500.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
        ];

        // Act
        $result = $this->action->execute($input);

        // Assert
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertEquals('Salário Atualizado', $result->name);
        $this->assertEquals(5500.00, $result->value);
        $this->assertEquals('income', $result->type);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'name' => 'Salário Atualizado',
            'value' => 5500.00,
            'type' => 'income',
        ]);
    }

    /**
     * @test
     * RF-07: Usuário NÃO pode atualizar receita de outro usuário.
     * A regra de validação do 'id' já filtra por user_id,
     * então a validação falha antes mesmo do Gate::authorize.
     */
    public function it_prevents_user_from_updating_others_income(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'type' => 'in',
        ]);

        $input = [
            'id' => $transaction->id,
            'name' => 'Tentativa de Update',
            'description' => 'Não deve funcionar',
            'value' => 999.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $otherCategory->id,
        ];

        // Assert - A validação do 'id' rejeita transações de outros usuários
        $this->expectException(ValidationException::class);

        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: transação deve existir.
     */
    public function it_requires_existing_transaction(): void
    {
        // Arrange
        $input = [
            'id' => 'uuid-inexistente',
            'name' => 'Teste',
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
        ];

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: transação deve ser do tipo 'in' (receita).
     */
    public function it_requires_income_type_transaction(): void
    {
        // Arrange
        $expenseTransaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out', // Despesa, não receita
        ]);

        $input = [
            'id' => $expenseTransaction->id,
            'name' => 'Teste',
            'description' => 'Teste',
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
        ];

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: categoria deve pertencer ao usuário autenticado.
     */
    public function it_requires_category_belonging_to_user(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'in',
        ]);

        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $input = [
            'id' => $transaction->id,
            'name' => 'Teste',
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $otherCategory->id,
        ];

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: valor deve ser maior que zero.
     */
    public function it_requires_value_greater_than_zero(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'in',
        ]);

        $input = [
            'id' => $transaction->id,
            'name' => 'Teste',
            'value' => 0,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
        ];

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->action->execute($input);
    }
}
