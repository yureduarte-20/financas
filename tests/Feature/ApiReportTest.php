<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests cannot access the reports endpoint.
     */
    public function test_guest_cannot_access_reports(): void
    {
        $this->getJson('/api/reports')->assertStatus(401);
    }

    /**
     * Test reports without filters returns all user's transactions with correct totals.
     */
    public function test_user_can_get_reports_without_filters(): void
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->forUser($user)->create(['name' => 'Salário']);
        $category2 = Category::factory()->forUser($user)->create(['name' => 'Aluguel']);

        // Create transactions
        Transaction::factory()->forUser($user)->forCategory($category1)->create([
            'value' => 3000.00,
            'type' => 'income',
            'expense_date' => '2026-06-01',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category1)->create([
            'value' => 500.00,
            'type' => 'income',
            'expense_date' => '2026-06-03',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category2)->create([
            'value' => 1200.00,
            'type' => 'out',
            'expense_date' => '2026-06-05',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'summary' => [
                        'total_income',
                        'total_expense',
                        'balance',
                    ],
                    'breakdown' => [
                        '*' => [
                            'category_id',
                            'category_name',
                            'total',
                            'count',
                        ]
                    ],
                    'transactions' => [
                        '*' => [
                            'id',
                            'name',
                            'value',
                            'type',
                            'expense_date',
                            'category',
                        ]
                    ]
                ]
            ]);

        $data = $response->json('data');

        $this->assertEquals(3500.00, $data['summary']['total_income']);
        $this->assertEquals(1200.00, $data['summary']['total_expense']);
        $this->assertEquals(2300.00, $data['summary']['balance']);
        $this->assertCount(3, $data['transactions']);

        // Check breakdown contains both categories
        $breakdown = collect($data['breakdown']);
        $salario = $breakdown->firstWhere('category_name', 'Salário');
        $aluguel = $breakdown->firstWhere('category_name', 'Aluguel');

        $this->assertNotNull($salario);
        $this->assertEquals(3500.00, $salario['total']);
        $this->assertEquals(2, $salario['count']);

        $this->assertNotNull($aluguel);
        $this->assertEquals(1200.00, $aluguel['total']);
        $this->assertEquals(1, $aluguel['count']);
    }

    /**
     * Test reports filtered by start date.
     */
    public function test_user_can_filter_reports_by_start_date(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 100.00,
            'type' => 'out',
            'expense_date' => '2026-06-01',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 200.00,
            'type' => 'out',
            'expense_date' => '2026-06-05',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?start_date=2026-06-03');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(0.0, $data['summary']['total_income']);
        $this->assertEquals(200.00, $data['summary']['total_expense']);
        $this->assertCount(1, $data['transactions']);
        $this->assertStringStartsWith('2026-06-05', $data['transactions'][0]['expense_date']);
    }

    /**
     * Test reports filtered by end date.
     */
    public function test_user_can_filter_reports_by_end_date(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 100.00,
            'type' => 'out',
            'expense_date' => '2026-06-01',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 200.00,
            'type' => 'out',
            'expense_date' => '2026-06-05',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?end_date=2026-06-03');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(100.00, $data['summary']['total_expense']);
        $this->assertCount(1, $data['transactions']);
        $this->assertStringStartsWith('2026-06-01', $data['transactions'][0]['expense_date']);
    }

    /**
     * Test reports filtered by date range.
     */
    public function test_user_can_filter_reports_by_date_range(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 100.00,
            'type' => 'out',
            'expense_date' => '2026-06-01',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 200.00,
            'type' => 'out',
            'expense_date' => '2026-06-05',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 300.00,
            'type' => 'out',
            'expense_date' => '2026-06-10',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?start_date=2026-06-03&end_date=2026-06-08');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(200.00, $data['summary']['total_expense']);
        $this->assertCount(1, $data['transactions']);
        $this->assertStringStartsWith('2026-06-05', $data['transactions'][0]['expense_date']);
    }

    /**
     * Test reports filtered by category.
     */
    public function test_user_can_filter_reports_by_category(): void
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->forUser($user)->create();
        $category2 = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category1)->create([
            'value' => 100.00,
            'type' => 'out',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category2)->create([
            'value' => 200.00,
            'type' => 'out',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?category_id=' . $category1->id);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(100.00, $data['summary']['total_expense']);
        $this->assertCount(1, $data['transactions']);
        $this->assertEquals($category1->id, $data['transactions'][0]['category_id']);
    }

    /**
     * Test reports filtered by type.
     */
    public function test_user_can_filter_reports_by_type(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 100.00,
            'type' => 'out',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->create([
            'value' => 500.00,
            'type' => 'income',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?type=income');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(500.00, $data['summary']['total_income']);
        $this->assertEquals(0.0, $data['summary']['total_expense']);
        $this->assertCount(1, $data['transactions']);
        $this->assertEquals('income', $data['transactions'][0]['type']);
    }

    /**
     * Test reports validation rules.
     */
    public function test_reports_validation_fails_with_invalid_parameters(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_device')->plainTextToken;

        // Invalid date format
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?start_date=not-a-date')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);

        // end_date before start_date
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?start_date=2026-06-10&end_date=2026-06-05')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);

        // Invalid type
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?type=invalid')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Category belonging to another user
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports?category_id=' . $otherCategory->id)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /**
     * Test reports excludes other users' transactions.
     */
    public function test_reports_excludes_other_users_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category1 = Category::factory()->forUser($user)->create();
        $category2 = Category::factory()->forUser($otherUser)->create();

        Transaction::factory()->forUser($user)->forCategory($category1)->create([
            'value' => 100.00,
            'type' => 'out',
        ]);
        Transaction::factory()->forUser($otherUser)->forCategory($category2)->create([
            'value' => 500.00,
            'type' => 'income',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(0.0, $data['summary']['total_income']);
        $this->assertEquals(100.00, $data['summary']['total_expense']);
        $this->assertCount(1, $data['transactions']);
    }
}
