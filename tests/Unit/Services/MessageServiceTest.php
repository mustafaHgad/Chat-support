<?php
namespace Tests\Unit\Services;

use Exception;
use Tests\TestCase;
use App\Services\MessageService;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Models\Chat;
use App\Models\Message;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $messageService;
    protected $messageRepository;
    protected $chatRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->chatRepository = Mockery::mock(ChatRepositoryInterface::class);

        $this->messageService = new MessageService(
            $this->messageRepository,
            $this->chatRepository
        );
    }

    public function test_send_message_successfully()
    {
        // Arrange
        $messageData = [
            'chat_id' => 1,
            'sender_id' => 1,
            'sender_type' => 'user',
            'sender_name' => 'أحمد محمد',
            'message' => 'مرحباً، أحتاج مساعدة',
            'message_type' => 'text'
        ];

        $chat = new Chat(['id' => 1, 'status' => 'active']);
        $expectedMessage = new Message($messageData);

        $this->chatRepository->shouldReceive('findById')
            ->with(1)
            ->andReturn($chat);

        $this->messageRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($messageData) {
                return $data['chat_id'] === $messageData['chat_id']
                    && $data['message'] === $messageData['message']
                    && $data['sender_type'] === $messageData['sender_type'];
            }))
            ->andReturn($expectedMessage);

        // Act
        $result = $this->messageService->sendMessage($messageData);

        // Assert
        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals('مرحباً، أحتاج مساعدة', $result->message);
    }

    public function test_send_message_fails_when_chat_not_found()
    {
        // Arrange
        $messageData = [
            'chat_id' => 999,
            'sender_type' => 'user',
            'sender_name' => 'أحمد محمد',
            'message' => 'Hello'
        ];

        $this->chatRepository->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Chat not found');

        $this->messageService->sendMessage($messageData);
    }

    public function test_get_chat_messages_returns_messages_successfully()
    {
        // Arrange
        $chatId = 1;
        $expectedMessages = collect([
            new Message(['message' => 'Message 1']),
            new Message(['message' => 'Message 2'])
        ]);

        $this->messageRepository->shouldReceive('getByChatId')
            ->once()
            ->with($chatId)
            ->andReturn($expectedMessages);

        // Act
        $result = $this->messageService->getChatMessages($chatId);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('Message 1', $result->first()->message);
    }

    public function test_mark_message_as_read_successfully()
    {
        // Arrange
        $messageId = 1;

        $this->messageRepository->shouldReceive('markAsRead')
            ->once()
            ->with($messageId)
            ->andReturn(true);

        // Act
        $result = $this->messageService->markMessageAsRead($messageId);

        // Assert
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
