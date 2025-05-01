<?php

namespace Tests\Feature\Api\Controller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Ticket;
use App\Models\Sale;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Test;

class TicketControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $ticket;

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
    public function it_lists_tickets_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Ticket::factory()->count(3)->create();

        $response = $this->getJson('/api/ticket');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([
                     '*' => ['id', 'titre', 'description', 'created_at', 'updated_at'],
                 ]);
    }

    #[Test]
    public function it_fails_to_list_tickets_without_auth()
    {
        Ticket::factory()->count(3)->create();

        $response = $this->getJson('/api/ticket');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_creates_a_new_ticket()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $sale = Sale::factory()->create(); // Crée un Sale valide

        $data = [
            'titre' => 'TicketTest',
            'description' => 'DescriptionTest',
            'sale_id' => $sale->id,
        ];

        $response = $this->postJson('/api/ticket', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'titre' => 'TicketTest',
                     'description' => 'DescriptionTest',
                 ])
                 ->assertJsonStructure(['id', 'titre', 'description', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('tickets', ['titre' => 'TicketTest']);
    }

    #[Test]
    public function it_fails_to_create_ticket_with_invalid_data()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'titre' => '', // Required field
            'sale_id' => 999, // Non-existent sale
        ];

        $response = $this->postJson('/api/ticket', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['titre', 'sale_id']);
    }

    #[Test]
    public function it_shows_a_ticket_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->ticket = Ticket::factory()->create();

        $response = $this->getJson("/api/ticket/{$this->ticket->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $this->ticket->id,
                     'titre' => $this->ticket->titre,
                     'description' => $this->ticket->description,
                 ])
                 ->assertJsonStructure(['id', 'titre', 'description', 'created_at', 'updated_at']);
    }

    #[Test]
    public function it_fails_to_show_nonexistent_ticket()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/ticket/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_updates_a_ticket_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $sale = Sale::factory()->create(); // Crée un Sale valide
        $this->ticket = Ticket::factory()->create();

        $data = [
            'titre' => 'UpdatedTicket',
            'description' => 'UpdatedDescription',
            'sale_id' => $sale->id, // Ajoute un sale_id valide
        ];

        $response = $this->putJson("/api/ticket/{$this->ticket->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'titre' => 'UpdatedTicket',
                     'description' => 'UpdatedDescription',
                 ])
                 ->assertJsonStructure(['id', 'titre', 'description', 'created_at', 'updated_at']);
    }

    #[Test]
    public function it_deletes_a_ticket_successfully()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->ticket = Ticket::factory()->create();

        $response = $this->deleteJson("/api/ticket/{$this->ticket->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tickets', ['id' => $this->ticket->id]);
    }

    #[Test]
    public function it_fails_to_delete_nonexistent_ticket()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->deleteJson('/api/ticket/999');

        $response->assertStatus(404);
    }
}