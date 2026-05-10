<?php

namespace Tests\Unit\Actions\Transaction;

use App\Actions\Transaction\CreateExpenseTransactionAction;
use App\Enum\TransactionStatusEnum;
use App\Models\Category;
use App\Models\Document;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Testes Unitários para CreateExpenseTransactionAction
 * 
 * Cobre RF-05: Cadastro manual de despesa
 */
class CreateExpenseTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateExpenseTransactionAction $action;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateExpenseTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        Auth::login($this->user);
    }

    /**
     * @test
     * RF-05: O sistema deve permitir o cadastro manual de uma despesa com valor, data, categoria e descrição.
     */
    public function it_can_create_an_expense_transaction(): void
    {
        // Arrange
        $input = [
            'value' => 150.50,
            'description' => 'Supermercado do mês',
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Supermercado ABC',
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'out',
            'value' => 150.50,
            'user_id' => $this->user->id,
            'status' => TransactionStatusEnum::PUBLISHED->value,
        ]);
        $this->assertEquals('Supermercado ABC', $transaction->name);
        $this->assertEquals('Supermercado do mês', $transaction->description);
    }

    /**
     * @test
     * Validação: valor deve ser obrigatório.
     */
    public function it_requires_value(): void
    {
        // Arrange
        $input = [
            'description' => 'Teste',
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
     * Validação: valor deve ser maior que zero.
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
     * Validação: valor deve ser um número válido.
     */
    public function it_requires_numeric_value(): void
    {
        // Arrange
        $input = [
            'value' => 'invalid',
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
     * Validação: data da despesa é obrigatória.
     */
    public function it_requires_expense_date(): void
    {
        // Arrange
        $input = [
            'value' => 100.00,
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
     * Validação: data deve ser uma data válida.
     */
    public function it_requires_valid_date(): void
    {
        // Arrange
        $input = [
            'value' => 100.00,
            'expense_date' => 'invalid-date',
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
     * Validação: categoria deve existir.
     */
    public function it_requires_existing_category(): void
    {
        // Arrange
        $input = [
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => 'invalid-uuid',
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
     * Validação: nome é obrigatório.
     */
    public function it_requires_name(): void
    {
        // Arrange
        $input = [
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
     * Validação: nome não pode exceder 255 caracteres.
     */
    public function it_validates_name_max_length(): void
    {
        // Arrange
        $input = [
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => str_repeat('A', 256),
        ];

        // Assert
        $this->expectException(ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Pode criar transação com documento vinculado.
     */
    public function it_can_create_transaction_with_document(): void
    {
        // Arrange
        $document = Document::factory()->create(['user_id' => $this->user->id]);
        
        $input = [
            'value' => 200.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Compra com nota',
            'document_id' => $document->id,
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'document_id' => $document->id,
        ]);
    }

    /**
     * @test
     * Pode criar transação com descrição opcional.
     */
    public function it_can_create_transaction_without_description(): void
    {
        // Arrange
        $input = [
            'value' => 50.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Despesa simples',
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertNull($transaction->description);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => null,
        ]);
    }
}
