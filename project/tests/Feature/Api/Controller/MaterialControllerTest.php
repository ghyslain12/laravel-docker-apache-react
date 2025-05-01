<?php

namespace Tests\Feature\Api\Controller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Material;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Test;

class MaterialControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $material; 

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
    public function it_lists_materials_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Material::factory()->count(3)->create();

        $response = $this->getJson('/api/material');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([
                     '*' => ['id', 'designation', 'created_at', 'updated_at'],
                 ]);
    }
/*
    #[Test]
    public function it_fails_to_list_materials_without_auth()
    {
        Material::factory()->count(3)->create();

        $response = $this->getJson('/api/material');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_creates_a_new_material()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'designation' => 'MaterialTest',
        ];

        $response = $this->postJson('/api/material', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'designation' => 'MaterialTest',
                 ])
                 ->assertJsonStructure(['id', 'designation', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('materials', ['designation' => 'MaterialTest']);
    }

    #[Test]
    public function it_fails_to_create_material_with_invalid_data()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'designation' => '', // Required field
        ];

        $response = $this->postJson('/api/material', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('designation');
    }

    #[Test]
    public function it_shows_a_material_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->material = Material::factory()->create();

        $response = $this->getJson("/api/material/{$this->material->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $this->material->id,
                     'designation' => $this->material->designation,
                 ])
                 ->assertJsonStructure(['id', 'designation', 'created_at', 'updated_at']);
    }

    #[Test]
    public function it_fails_to_show_nonexistent_material()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/material/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_updates_a_material_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->material = Material::factory()->create();

        $data = [
            'designation' => 'UpdatedMaterial',
        ];

        $response = $this->putJson("/api/material/{$this->material->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'designation' => 'UpdatedMaterial',
                 ])
                 ->assertJsonStructure(['id', 'designation', 'created_at', 'updated_at']);
    }

    #[Test]
    public function it_deletes_a_material_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->material = Material::factory()->create();

        $response = $this->deleteJson("/api/material/{$this->material->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('materials', ['id' => $this->material->id]);
    }

    #[Test]
    public function it_fails_to_delete_nonexistent_material()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->deleteJson('/api/material/999');

        $response->assertStatus(404);
    }*/
}