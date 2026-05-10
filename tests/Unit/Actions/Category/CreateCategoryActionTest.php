<?php

namespace Tests\Unit\Actions\Category;

use App\Actions\Category\CreateCategoryAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Testes Unitários para CreateCategoryAction
 * 
 * Cobre RF-21, RF-22: CRUD de categorias
 */
class CreateCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateCategoryAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateCategoryAction();
        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    /**
     * @test
     * RF-21, RF-22: O sistema deve permitir criar categorias personalizadas.
     */
    public function it_can_create_a_category_with_valid_data(): void
    {
        // Arrange
        $input = [
            'name' => 'Viagens',
            'description' => 'Gastos com viagens e turismo',
        ];

        // Act
        $category = $this->action->execute($input);

        // Assert
        $this->assertInstanceOf(Category::class, $category);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Viagens',
            'description' => 'Gastos com viagens e turismo',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * Categoria pode ser criada apenas com nome (descrição opcional).
     */
    public function it_can_create_category_with_name_only(): void
    {
        // Arrange
        $input = [
            'name' => 'Educação',
        ];

        // Act
        $category = $this->action->execute($input);

        // Assert
        $this->assertNull($category->description);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Educação',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * Validação: nome é obrigatório.
     */
    public function it_requires_name(): void
    {
        // Arrange
        $input = [
            'name' => '',
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
            'name' => str_repeat('A', 256),
        ];

        // Assert
        $this->expectException(ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: descrição deve ter no mínimo 3 caracteres quando presente.
     */
    public function it_validates_description_minimum_length(): void
    {
        // Arrange
        $input = [
            'name' => 'Teste',
            'description' => 'AB',
        ];

        // Assert
        $this->expectException(ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Categoria é vinculada ao usuário autenticado.
     */
    public function it_assigns_category_to_authenticated_user(): void
    {
        // Arrange
        $input = [
            'name' => 'Saúde',
        ];

        // Act
        $category = $this->action->execute($input);

        // Assert
        $this->assertEquals($this->user->id, $category->user_id);
    }

    /**
     * @test
     * Usuário deve ter autorização para criar categoria.
     */
    public function it_requires_authorization_to_create_category(): void
    {
        // Arrange - Criar usuário sem permissão
        $unauthorizedUser = User::factory()->create();
        Auth::login($unauthorizedUser);

        // Simular gate negando acesso
        \Illuminate\Support\Facades\Gate::shouldReceive('authorize')
            ->with('create', Category::class)
            ->andThrow(new AuthorizationException('Não autorizado'));

        $input = [
            'name' => 'Teste',
        ];

        // Assert
        $this->expectException(AuthorizationException::class);

        // Act
        $this->action->execute($input);
    }
}
