<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_access_agent_routes()
    {
        // Arrange
        $agent = User::factory()->agent()->create();
        Sanctum::actingAs($agent);

        // Act & Assert
        $this->getJson('/api/agent/chats/waiting')->assertStatus(200);
        $this->getJson('/api/agent/chats/active')->assertStatus(200);
        $this->getJson('/api/agent/statistics')->assertStatus(200);
    }

    public function test_regular_user_cannot_access_agent_routes()
    {
        // Arrange
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        // Act & Assert
        $this->getJson('/api/agent/chats/waiting')->assertStatus(403);
        $this->getJson('/api/agent/chats/active')->assertStatus(403);
        $this->getJson('/api/agent/statistics')->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        // Act & Assert
        $this->getJson('/api/agent/chats/waiting')->assertStatus(401);
        $this->getJson('/api/user/chats')->assertStatus(401);
        $this->getJson('/api/auth/user')->assertStatus(401);
    }

    public function test_guests_can_access_public_chat_routes()
    {
        // Arrange
        $guestData = [
            'guest_name' => 'زائر تجريبي',
            'guest_email' => 'guest@example.com'
        ];

        // Act & Assert - Start chat
        $response = $this->postJson('/api/chat/start', $guestData);
        $response->assertStatus(201);

        $sessionId = $response->json('data.session_id');

        // Act & Assert - Get messages
        $this->getJson("/api/chat/{$sessionId}/messages")->assertStatus(200);
    }

    public function test_rate_limiting_works_on_public_endpoints()
    {
        // Arrange
        $guestData = [
            'guest_name' => 'Test Guest',
            'guest_email' => 'test@example.com'
        ];

        // Act - Make multiple requests rapidly
        for ($i = 0; $i < 65; $i++) { // Exceed rate limit of 60 per minute
            $response = $this->postJson('/api/chat/start', $guestData);

            if ($i < 60) {
                $response->assertStatus(201);
            } else {
                $response->assertStatus(429); // Too Many Requests
                break;
            }
        }
    }
}
