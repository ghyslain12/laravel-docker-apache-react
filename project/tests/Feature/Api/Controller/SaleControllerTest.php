<?php

namespace Tests\Feature\Api\Controller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Test;

class SaleControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $sale;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function authenticate($user)
    {
        if (env('JWT_ENABLE', false)) {
            // Avec JWT personnalisé
            $token = $this->generateJwtToken($user->id);
            return $this->withHeaders(['Authorization' => "Bearer $token"]);
        } else {
            // Avec Sanctum
            Sanctum::actingAs($user, ['*']);
            return $this;
        }
    }

    private function generateJwtToken($userId)
    {
        $key = env('JWT_SECRET');
        if (!$key) {
            throw new \Exception('JWT_SECRET is not defined in .env.testing');
        }
        $payload = [
            'iss' => 'laravel',
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        return JWT::encode($payload, $key, 'HS256');
    }

    #[Test]
    public function it_lists_sales_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Sale::factory()->count(3)->create();

        $response = $this->getJson('/api/sale');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([
                     '*' => ['id', 'titre', 'description', 'customer_id', 'created_at', 'updated_at'],
                 ]);
    }

    #[Test]
    public function it_fails_to_list_sales_without_auth()
    {
        Sale::factory()->count(3)->create();

        $response = $this->getJson('/api/sale');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_creates_a_new_sale()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $data = [
            'titre' => 'SaleTest',
            'description' => 'DescriptionTest',
            'customer_id' => $customer->id,
        ];

        $response = $this->postJson('/api/sale', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'titre' => 'SaleTest',
                     'description' => 'DescriptionTest',
                     'customer_id' => $customer->id,
                 ])
                 ->assertJsonStructure(['id', 'titre', 'description', 'customer_id', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('sales', ['titre' => 'SaleTest']);
    }

    #[Test]
    public function it_fails_to_create_sale_with_invalid_data()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'titre' => '',
            'customer_id' => 999,
        ];

        $response = $this->postJson('/api/sale', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['titre', 'customer_id']);
    }

    #[Test]
    public function it_shows_a_sale_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->sale = Sale::factory()->create();

        $response = $this->getJson("/api/sale/{$this->sale->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $this->sale->id,
                     'titre' => $this->sale->titre,
                     'description' => $this->sale->description,
                     'customer_id' => $this->sale->customer_id,
                 ])
                 ->assertJsonStructure(['id', 'titre', 'description', 'customer_id', 'created_at', 'updated_at']);
    }

    #[Test]
    public function it_fails_to_show_nonexistent_sale()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/sale/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_updates_a_sale_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->sale = Sale::factory()->create();
        $customer = Customer::factory()->create();

        $data = [
            'titre' => 'UpdatedSale',
            'description' => 'UpdatedDescription',
            'customer_id' => $customer->id,
        ];

        $response = $this->putJson("/api/sale/{$this->sale->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'titre' => 'UpdatedSale',
                     'description' => 'UpdatedDescription',
                     'customer_id' => $customer->id,
                 ])
                 ->assertJsonStructure(['id', 'titre', 'description', 'customer_id', 'created_at', 'updated_at']);
    }

    #[Test]
    public function it_deletes_a_sale_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->sale = Sale::factory()->create();

        $response = $this->deleteJson("/api/sale/{$this->sale->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('sales', ['id' => $this->sale->id]);
    }

    #[Test]
    public function it_fails_to_delete_nonexistent_sale()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->deleteJson('/api/sale/999');

        $response->assertStatus(404);
    }
}