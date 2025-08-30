<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_start_chat()
    {
        // Arrange
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->postJson('/api/chat/start');

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'session_id',
                    'status',
                    'user' => ['id', 'name', 'email'],
                    'created_at'
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'waiting'
                ]
            ]);

        $this->assertDatabaseHas('chats', [
            'user_id' => $user->id,
            'status' => 'waiting'
        ]);
    }

    public function test_guest_can_start_chat_with_required_info()
    {
        // Arrange
        $guestData = [
            'guest_name' => 'أحمد محمد',
            'guest_email' => 'ahmed@example.com'
        ];

        // Act
        $response = $this->postJson('/api/chat/start', $guestData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'session_id',
                    'status',
                    'guest_info' => ['guest_name', 'guest_email'],
                    'created_at'
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'waiting',
                    'guest_info' => $guestData
                ]
            ]);

        $this->assertDatabaseHas('chats', [
            'guest_name' => 'أحمد محمد',
            'guest_email' => 'ahmed@example.com',
            'status' => 'waiting'
        ]);
    }

    public function test_guest_start_chat_fails_without_required_info()
    {
        // Act
        $response = $this->postJson('/api/chat/start', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name', 'guest_email']);
    }

    public function test_get_chat_messages_returns_correct_data()
    {
        // Arrange
        $chat = Chat::factory()->withMessages(3)->create();

        // Act
        $response = $this->getJson("/api/chat/{$chat->session_id}/messages");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'chat' => ['id', 'session_id', 'status'],
                    'messages' => [
                        '*' => [
                            'id',
                            'sender_name',
                            'sender_type',
                            'message',
                            'message_type',
                            'sent_at'
                        ]
                    ]
                ]
            ])
            ->assertJson(['success' => true]);

        $this->assertEquals($chat->id, $response->json('data.chat.id'));
        $this->assertCount(3, $response->json('data.messages'));
    }

    public function test_get_chat_messages_fails_for_invalid_session()
    {
        // Act
        $response = $this->getJson('/api/chat/invalid_session_id/messages');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Chat session not found'
            ]);
    }

    public function test_agent_can_get_waiting_chats()
    {
        // Arrange
        $agent = User::factory()->agent()->create();
        Sanctum::actingAs($agent);

        Chat::factory()->waiting()->count(3)->create();
        Chat::factory()->active()->count(2)->create();

        // Act
        $response = $this->getJson('/api/agent/chats/waiting');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'session_id', 'status']
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_non_agent_cannot_access_agent_endpoints()
    {
        // Arrange
        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson('/api/agent/chats/waiting');

        // Assert
        $response->assertStatus(403);
    }
}
