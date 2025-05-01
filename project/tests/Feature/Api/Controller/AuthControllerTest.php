<?php

namespace Tests\Feature\Api\Controller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => 'zm4ZkgOBEW5BTG3oyuYfzIlioTreEjbUCdrETAZqAGE=']);
        $this->artisan('config:clear');
    }

    #[Test]
    public function it_logs_in_user_and_returns_jwt_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);

        $token = $response->json('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));

        $this->assertEquals('votre_domaine', $decoded->iss);
        $this->assertEquals($user->id, $decoded->sub);
        $this->assertTrue($decoded->exp > time());

        [$headerEncoded] = explode('.', $token);
        $header = json_decode(base64_decode($headerEncoded));
        $this->assertEquals('HS256', $header->alg);
    }

    #[Test]
    public function it_fails_to_login_with_invalid_email()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Identifiants invalides']);
    }

    #[Test]
    public function it_fails_to_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Identifiants invalides']);
    }

    #[Test]
    public function it_fails_to_login_with_missing_credentials()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Email et mot de passe requis']);
    }

    #[Test]
    public function it_fails_to_login_without_jwt_secret()
    {
        // Vide la clé JWT via config
        config(['jwt.secret' => null]);

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(500)
                 ->assertJsonFragment(['error' => 'Configuration JWT invalide']);
    }

    #[Test]
    public function it_returns_token_with_correct_expiration()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $response->json('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));

        $this->assertEqualsWithDelta(time() + 3600, $decoded->exp, 5);
    }
}