<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\Eloquent\ChatRepository;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $chatRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatRepository = new ChatRepository(new Chat());
    }

    public function test_create_chat_successfully()
    {
        // Arrange
        $data = [
            'session_id' => 'chat_test_123',
            'user_id' => User::factory()->create()->id,
            'status' => 'waiting'
        ];

        // Act
        $result = $this->chatRepository->create($data);

        // Assert
        $this->assertInstanceOf(Chat::class, $result);
        $this->assertEquals('chat_test_123', $result->session_id);
        $this->assertEquals('waiting', $result->status);
        $this->assertDatabaseHas('chats', ['session_id' => 'chat_test_123']);
    }

    public function test_find_by_session_id_returns_correct_chat()
    {
        // Arrange
        $chat = Chat::factory()->create(['session_id' => 'chat_unique_123']);

        // Act
        $result = $this->chatRepository->findBySessionId('chat_unique_123');

        // Assert
        $this->assertInstanceOf(Chat::class, $result);
        $this->assertEquals('chat_unique_123', $result->session_id);
        $this->assertEquals($chat->id, $result->id);
    }

    public function test_update_status_changes_chat_status()
    {
        // Arrange
        $chat = Chat::factory()->create(['status' => 'waiting']);

        // Act
        $result = $this->chatRepository->updateStatus($chat->id, 'active');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'status' => 'active'
        ]);
    }

    public function test_assign_agent_updates_chat_correctly()
    {
        // Arrange
        $chat = Chat::factory()->create(['status' => 'waiting', 'agent_id' => null]);
        $agent = User::factory()->agent()->create();

        // Act
        $result = $this->chatRepository->assignAgent($chat->id, $agent->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'agent_id' => $agent->id,
            'status' => 'active'
        ]);
    }

    public function test_get_waiting_chats_returns_only_waiting_chats()
    {
        // Arrange
        Chat::factory()->waiting()->count(3)->create();
        Chat::factory()->active()->count(2)->create();
        Chat::factory()->closed()->count(1)->create();

        // Act
        $result = $this->chatRepository->getWaitingChats();

        // Assert
        $this->assertCount(3, $result);
        $result->each(function ($chat) {
            $this->assertEquals('waiting', $chat->status);
        });
    }

    public function test_get_active_chats_returns_only_active_chats()
    {
        // Arrange
        Chat::factory()->waiting()->count(2)->create();
        Chat::factory()->active()->count(4)->create();
        Chat::factory()->closed()->count(1)->create();

        // Act
        $result = $this->chatRepository->getActiveChats();

        // Assert
        $this->assertCount(4, $result);
        $result->each(function ($chat) {
            $this->assertEquals('active', $chat->status);
        });
    }
}
