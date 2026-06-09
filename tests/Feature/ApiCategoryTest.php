<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests cannot access any category endpoints.
     */
    public function test_guest_cannot_access_category_endpoints(): void
    {
        // Index
        $response = $this->getJson('/api/categories');
        $response->assertStatus(401);

        // Store
        $response = $this->postJson('/api/categories', ['name' => 'Test']);
        $response->assertStatus(401);

        // Show
        $response = $this->getJson('/api/categories/some-uuid');
        $response->assertStatus(401);

        // Update
        $response = $this->putJson('/api/categories/some-uuid', ['name' => 'Updated']);
        $response->assertStatus(401);

        // Destroy
        $response = $this->deleteJson('/api/categories/some-uuid');
        $response->assertStatus(401);
    }

    /**
     * Test list only returns the authenticated user's categories ordered by name.
     */
    public function test_user_can_list_own_categories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create categories for target user (names deliberately out of alphabetical order)
        Category::factory()->forUser($user)->create(['name' => 'Transporte']);
        Category::factory()->forUser($user)->create(['name' => 'Alimentação']);
        Category::factory()->forUser($user)->create(['name' => 'Lazer']);

        // Create category for another user
        Category::factory()->forUser($otherUser)->create(['name' => 'Outro']);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.name', 'Alimentação')
            ->assertJsonPath('data.1.name', 'Lazer')
            ->assertJsonPath('data.2.name', 'Transporte');

        // Verify that the other user's category is not present
        $names = collect($response->json('data'))->pluck('name');
        $this->assertNotContains('Outro', $names);
    }

    /**
     * Test user can view their own category.
     */
    public function test_user_can_view_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create([
            'name' => 'Comida',
            'description' => 'Gastos com alimentação diária',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'user_id', 'created_at', 'updated_at']])
            ->assertJsonPath('data.name', 'Comida')
            ->assertJsonPath('data.description', 'Gastos com alimentação diária');
    }

    /**
     * Test user cannot view another user's category.
     */
    public function test_user_cannot_view_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/categories/{$category->id}");

        $response->assertStatus(403);
    }

    /**
     * Test creating a category via API.
     */
    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'name' => 'Educação',
                'description' => 'Mensalidade e livros',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'name', 'description', 'user_id']])
            ->assertJsonPath('message', 'Categoria criada com sucesso.')
            ->assertJsonPath('data.name', 'Educação')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('categories', [
            'name' => 'Educação',
            'description' => 'Mensalidade e livros',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test validation fails when missing or invalid fields.
     */
    public function test_create_category_validation_fails(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_device')->plainTextToken;

        // Missing name
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'description' => 'Test description',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Description too short (minimum 3 chars as defined in CreateCategoryAction)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'name' => 'Lazer',
                'description' => 'Oi',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    /**
     * Test updating own category.
     */
    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create([
            'name' => 'Supermercado',
            'description' => 'Gastos mensais',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/categories/{$category->id}", [
                'name' => 'Supermercado e Feira',
                'description' => 'Gastos mensais alterados',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('message', 'Categoria atualizada com sucesso.')
            ->assertJsonPath('data.name', 'Supermercado e Feira')
            ->assertJsonPath('data.description', 'Gastos mensais alterados');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Supermercado e Feira',
            'description' => 'Gastos mensais alterados',
        ]);
    }

    /**
     * Test user cannot update another user's category.
     */
    public function test_user_cannot_update_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create([
            'name' => 'Viagem',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/categories/{$category->id}", [
                'name' => 'Viagem de Luxo',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Viagem',
        ]);
    }

    /**
     * Test deleting own category.
     */
    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Categoria excluída com sucesso.');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test user cannot delete another user's category.
     */
    public function test_user_cannot_delete_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
