<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Tests\TestCase;

/**
 * Testes Unitários para RegisterUserAction
 * 
 * Cobre RF-01: Cadastro de usuário com nome, e-mail e senha
 */
class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    private RegisterUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RegisterUserAction();
    }

    /**
     * @test
     * RF-01: O sistema deve permitir o cadastro do usuário com nome, e-mail e senha.
     */
    public function it_can_register_a_new_user_with_valid_data(): void
    {
        // Arrange
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'senhaSegura123',
        ];

        // Act
        $user = $this->action->execute($data);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);
        $this->assertTrue(Hash::check('senhaSegura123', $user->password));
    }

    /**
     * @test
     * RF-21: Categoria padrão deve ser criada automaticamente.
     */
    public function it_creates_default_category_for_new_user(): void
    {
        // Arrange
        $data = [
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'password' => 'senhaSegura456',
        ];

        // Act
        $user = $this->action->execute($data);

        // Assert
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Outros',
        ]);
    }

    /**
     * @test
     * Evento Registered deve ser disparado.
     */
    public function it_dispatches_registered_event(): void
    {
        // Arrange
        Event::fake([Registered::class]);
        
        $data = [
            'name' => 'Pedro Costa',
            'email' => 'pedro@example.com',
            'password' => 'senhaSegura789',
        ];

        // Act
        $this->action->execute($data);

        // Assert
        Event::assertDispatched(Registered::class);
    }

    /**
     * @test
     * E-mail deve ser armazenado em minúsculas.
     */
    public function it_stores_email_in_lowercase(): void
    {
        // Arrange
        $data = [
            'name' => 'Test User',
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'senhaSegura123',
        ];

        // Act
        $user = $this->action->execute($data);

        // Assert
        $this->assertEquals('test@example.com', $user->email);
    }
}
