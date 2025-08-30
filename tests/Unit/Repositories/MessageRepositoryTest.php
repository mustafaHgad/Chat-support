<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\Eloquent\MessageRepository;
use App\Models\Message;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $messageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageRepository = new MessageRepository(new Message());
    }

    public function test_create_message_successfully()
    {
        // Arrange
        $chat = Chat::factory()->create();
        $user = User::factory()->create();

        $data = [
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'sender_type' => 'user',
            'sender_name' => $user->name,
            'message' => 'مرحباً، أحتاج مساعدة',
            'message_type' => 'text',
            'sent_at' => now()
        ];

        // Act
        $result = $this->messageRepository->create($data);

        // Assert
        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals('مرحباً، أحتاج مساعدة', $result->message);
        $this->assertEquals($chat->id, $result->chat_id);
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'message' => 'مرحباً، أحتاج مساعدة'
        ]);
    }

    public function test_get_by_chat_id_returns_messages_in_correct_order()
    {
        // Arrange
        $chat = Chat::factory()->create();

        $message1 = Message::factory()->create([
            'chat_id' => $chat->id,
            'message' => 'First message',
            'sent_at' => now()->subMinutes(10)
        ]);

        $message2 = Message::factory()->create([
            'chat_id' => $chat->id,
            'message' => 'Second message',
            'sent_at' => now()->subMinutes(5)
        ]);

        // Act
        $result = $this->messageRepository->getByChatId($chat->id);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('First message', $result->first()->message);
        $this->assertEquals('Second message', $result->last()->message);
    }

    public function test_mark_as_read_updates_message_status()
    {
        // Arrange
        $message = Message::factory()->unread()->create();

        // Act
        $result = $this->messageRepository->markAsRead($message->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'is_read' => true
        ]);
    }

    public function test_get_unread_count_returns_correct_count()
    {
        // Arrange
        $chat = Chat::factory()->create();
        Message::factory()->read()->count(3)->create(['chat_id' => $chat->id]);
        Message::factory()->unread()->count(2)->create(['chat_id' => $chat->id]);

        // Act
        $result = $this->messageRepository->getUnreadCount($chat->id);

        // Assert
        $this->assertEquals(2, $result);
    }
}
