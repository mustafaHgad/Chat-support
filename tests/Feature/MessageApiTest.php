<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_message_to_their_chat()
    {
        // Arrange
        $user = User::factory()->customer()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $messageData = [
            'chat_id' => $chat->id,
            'sender_type' => 'user',
            'sender_name' => $user->name,
            'message' => 'Hello, I need help with the product.',
            'message_type' => 'text'
        ];

        // Act
        $response = $this->postJson('/api/messages/send', $messageData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'chat_id',
                    'sender_name',
                    'message',
                    'message_type',
                    'sent_at'
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'chat_id' => $chat->id,
                    'message' => 'Hello, I need help with the product.'
                ]
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'message' => 'Hello, I need help with the product.',
            'sender_type' => 'user'
        ]);
    }

    public function test_guest_can_send_message_to_their_chat()
    {
        // Arrange
        $chat = Chat::factory()->guest()->create();

        $messageData = [
            'chat_id' => $chat->id,
            'sender_type' => 'guest',
            'sender_name' => $chat->guest_name,
            'message' => 'مرحباً، أحتاج مساعدة',
            'message_type' => 'text'
        ];

        // Act
        $response = $this->postJson('/api/messages/send', $messageData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'chat_id' => $chat->id,
                    'sender_type' => 'guest'
                ]
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'sender_type' => 'guest',
            'message' => 'مرحباً، أحتاج مساعدة'
        ]);
    }

    public function test_agent_can_send_message_to_assigned_chat()
    {
        // Arrange
        $agent = User::factory()->agent()->create();
        $chat = Chat::factory()->active()->create(['agent_id' => $agent->id]);
        Sanctum::actingAs($agent);

        $messageData = [
            'chat_id' => $chat->id,
            'sender_type' => 'agent',
            'sender_name' => $agent->name,
            'message' => 'مرحباً! كيف يمكنني مساعدتك؟',
            'message_type' => 'text'
        ];

        // Act
        $response = $this->postJson('/api/messages/send', $messageData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sender_type' => 'agent',
                    'message' => 'مرحباً! كيف يمكنني مساعدتك؟'
                ]
            ]);
    }

    public function test_send_message_fails_with_invalid_chat_id()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $messageData = [
            'chat_id' => 999999,
            'sender_type' => 'user',
            'sender_name' => $user->name,
            'message' => 'Test message'
        ];

        // Act
        $response = $this->postJson('/api/messages/send', $messageData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chat_id']);
    }

    public function test_send_message_fails_with_missing_required_fields()
    {
        // Act
        $response = $this->postJson('/api/messages/send', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'chat_id',
                //'sender_type',
                //'sender_name',
                'message'
            ]);
    }

    public function test_send_message_fails_with_invalid_sender_type()
    {
        // Arrange
        $chat = Chat::factory()->create();

        $messageData = [
            'chat_id' => $chat->id,
            'sender_type' => 'invalid_type',
            'sender_name' => 'Test User',
            'message' => 'Test message'
        ];

        // Act
        $response = $this->postJson('/api/messages/send', $messageData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sender_type']);
    }
}
