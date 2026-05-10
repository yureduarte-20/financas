<?php

namespace Tests\Unit\Actions\Transaction;

use App\Actions\Transaction\CreateIncomeTransactionAction;
use App\Enum\TransactionStatusEnum;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Testes Unitários para CreateIncomeTransactionAction
 * 
 * Cobre RF-06: Cadastro manual de receita
 */
class CreateIncomeTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateIncomeTransactionAction $action;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateIncomeTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        Auth::login($this->user);
    }

    /**
     * @test
     * RF-06: O sistema deve permitir o cadastro manual de uma receita com valor, data, categoria e descrição.
     */
    public function it_can_create_an_income_transaction(): void
    {
        // Arrange
        $input = [
            'value' => 5000.00,
            'description' => 'Salário mensal',
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Salário',
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'income',
            'value' => 5000.00,
            'user_id' => $this->user->id,
            'status' => TransactionStatusEnum::PUBLISHED->value,
        ]);
    }

    /**
     * @test
     * Transação de receita deve ter tipo 'income'.
     */
    public function it_creates_income_with_type_in(): void
    {
        // Arrange
        $input = [
            'value' => 1000.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Freelance',
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertEquals('income', $transaction->type);
    }

    /**
     * @test
     * Validação: valor deve ser obrigatório e maior que zero.
     */
    public function it_requires_value_greater_than_zero(): void
    {
        // Arrange
        $input = [
            'value' => 0,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Teste',
        ];

        // Assert
        $this->expectException(ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: valor não pode ser negativo.
     */
    public function it_rejects_negative_value(): void
    {
        // Arrange
        $input = [
            'value' => -100,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Teste',
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
    public function it_requires_category_belonging_to_authenticated_user(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $input = [
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $otherCategory->id,
            'name' => 'Teste',
        ];

        // Assert
        $this->expectException(ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Receita pode ser criada com descrição opcional.
     */
    public function it_allows_null_description(): void
    {
        // Arrange
        $input = [
            'value' => 2000.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Bônus',
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertNull($transaction->description);
    }
}
