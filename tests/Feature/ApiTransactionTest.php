<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests cannot access any transaction endpoints.
     */
    public function test_guest_cannot_access_transaction_endpoints(): void
    {
        // Index
        $this->getJson('/api/transactions')->assertStatus(401);

        // Store
        $this->postJson('/api/transactions', ['name' => 'Test'])->assertStatus(401);

        // Show
        $this->getJson('/api/transactions/some-uuid')->assertStatus(401);

        // Update
        $this->putJson('/api/transactions/some-uuid', ['name' => 'Updated'])->assertStatus(401);

        // Destroy
        $this->deleteJson('/api/transactions/some-uuid')->assertStatus(401);
    }

    /**
     * Test list returns the authenticated user's transactions, ordered by date.
     */
    public function test_user_can_list_own_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::factory()->forUser($user)->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();

        // Create transactions for target user
        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'First Expense',
            'expense_date' => '2026-06-01',
            'type' => 'out',
        ]);

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Second Income',
            'expense_date' => '2026-06-03',
            'type' => 'income',
        ]);

        // Create transaction for another user
        Transaction::factory()->forUser($otherUser)->forCategory($otherCategory)->create([
            'name' => 'Other Expense',
            'expense_date' => '2026-06-02',
            'type' => 'out',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Second Income') // Sorted desc by expense_date
            ->assertJsonPath('data.1.name', 'First Expense')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'value',
                        'type',
                        'expense_date',
                        'category' => [
                            'id',
                            'name',
                        ]
                    ]
                ]
            ]);

        // Verify other user's transaction is not returned
        $names = collect($response->json('data'))->pluck('name');
        $this->assertNotContains('Other Expense', $names);
    }

    /**
     * Test list filtering by type=out.
     */
    public function test_user_can_list_transactions_filtered_by_type_out(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Expense 1',
            'type' => 'out',
        ]);

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Income 1',
            'type' => 'income',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/transactions?type=out');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Expense 1');
    }

    /**
     * Test list filtering by type=income.
     */
    public function test_user_can_list_transactions_filtered_by_type_income(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Expense 1',
            'type' => 'out',
        ]);

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Income 1',
            'type' => 'income',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/transactions?type=income');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Income 1');
    }

    /**
     * Test user can view their own transaction details.
     */
    public function test_user_can_view_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create(['name' => 'Salário']);
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Salário Mensal',
            'type' => 'income',
            'value' => 5000.00,
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Salário Mensal')
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.category.name', 'Salário');
    }

    /**
     * Test user cannot view another user's transaction.
     */
    public function test_user_cannot_view_other_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();
        $transaction = Transaction::factory()->forUser($otherUser)->forCategory($category)->create();

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }

    /**
     * Test creating an expense transaction.
     */
    public function test_user_can_create_expense_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions', [
                'type' => 'out',
                'name' => 'Almoço Executivo',
                'value' => 45.50,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
                'description' => 'Restaurante Sabor',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Transação criada com sucesso.')
            ->assertJsonStructure(['message', 'data' => ['id', 'name', 'value', 'type', 'category_id']]);

        $this->assertDatabaseHas('transactions', [
            'name' => 'Almoço Executivo',
            'type' => 'out',
            'value' => 45.50,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test creating an income transaction.
     */
    public function test_user_can_create_income_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions', [
                'type' => 'income',
                'name' => 'Freela PHP',
                'value' => 1200.00,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
                'description' => 'Website simples',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Transação criada com sucesso.')
            ->assertJsonPath('data.type', 'income');

        $this->assertDatabaseHas('transactions', [
            'name' => 'Freela PHP',
            'type' => 'income',
            'value' => 1200.00,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test validation fails when type is invalid.
     */
    public function test_create_transaction_fails_on_invalid_type(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions', [
                'type' => 'invalid-type',
                'name' => 'Almoço Executivo',
                'value' => 45.50,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test validation fails when category belongs to another user.
     */
    public function test_create_transaction_fails_when_category_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();
        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions', [
                'type' => 'out',
                'name' => 'Almoço Executivo',
                'value' => 45.50,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /**
     * Test updating own expense transaction.
     */
    public function test_user_can_update_own_expense_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Gasolina',
            'type' => 'out',
            'value' => 100.00,
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/transactions/{$transaction->id}", [
                'name' => 'Gasolina Aditivada',
                'value' => 150.00,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
                'description' => 'Tanque cheio',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Transação atualizada com sucesso.')
            ->assertJsonPath('data.name', 'Gasolina Aditivada');

        $this->assertEquals(150.00, $response->json('data.value'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'name' => 'Gasolina Aditivada',
            'value' => 150.00,
        ]);
    }

    /**
     * Test updating own income transaction.
     */
    public function test_user_can_update_own_income_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create([
            'name' => 'Rendimentos',
            'type' => 'income',
            'value' => 50.00,
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/transactions/{$transaction->id}", [
                'name' => 'Dividendos',
                'value' => 80.00,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
                'description' => 'FIIs',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Transação atualizada com sucesso.')
            ->assertJsonPath('data.name', 'Dividendos');

        $this->assertEquals(80.00, $response->json('data.value'));
    }

    /**
     * Test user cannot update another user's transaction.
     */
    public function test_user_cannot_update_other_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();
        $transaction = Transaction::factory()->forUser($otherUser)->forCategory($category)->create([
            'name' => 'Original',
            'type' => 'out',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/transactions/{$transaction->id}", [
                'name' => 'Tentativa de Update',
                'value' => 200.00,
                'expense_date' => '2026-06-09',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test deleting own expense transaction.
     */
    public function test_user_can_delete_own_expense_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create([
            'type' => 'out',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Transação excluída com sucesso.');

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * Test deleting own income transaction.
     */
    public function test_user_can_delete_own_income_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create([
            'type' => 'income',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Transação excluída com sucesso.');

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * Test user cannot delete another user's transaction.
     */
    public function test_user_cannot_delete_other_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();
        $transaction = Transaction::factory()->forUser($otherUser)->forCategory($category)->create();

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
        ]);
    }
}
