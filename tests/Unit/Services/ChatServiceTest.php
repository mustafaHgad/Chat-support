<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ChatService;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Models\Chat;
use App\Models\User;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $chatService;
    protected $chatRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chatRepository = Mockery::mock(ChatRepositoryInterface::class);
        $this->chatService = new ChatService($this->chatRepository);
    }

    public function test_start_chat_for_user_creates_session_successfully()
    {
        // Arrange
        $userId = 1;
        $expectedChat = new Chat([
            'session_id' => 'chat_test_uuid',
            'user_id' => $userId,
            'status' => 'waiting'
        ]);

        $this->chatRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userId) {
                return $data['user_id'] === $userId
                    && $data['status'] === 'waiting'
                    && str_starts_with($data['session_id'], 'chat_');
            }))
            ->andReturn($expectedChat);

        // Act
        $result = $this->chatService->startChatForUser($userId);

        // Assert
        $this->assertInstanceOf(Chat::class, $result);
        $this->assertEquals($userId, $result->user_id);
        $this->assertEquals('waiting', $result->status);
    }

    public function test_start_chat_for_guest_creates_session_successfully()
    {
        // Arrange
        $guestName = 'أحمد محمد';
        $guestEmail = 'ahmed@example.com';

        $expectedChat = new Chat([
            'session_id' => 'chat_test_uuid',
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'status' => 'waiting'
        ]);

        $this->chatRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($guestName, $guestEmail) {
                return $data['guest_name'] === $guestName
                    && $data['guest_email'] === $guestEmail
                    && $data['status'] === 'waiting';
            }))
            ->andReturn($expectedChat);

        // Act
        $result = $this->chatService->startChatForGuest($guestName, $guestEmail);

        // Assert
        $this->assertInstanceOf(Chat::class, $result);
        $this->assertEquals($guestName, $result->guest_name);
        $this->assertEquals($guestEmail, $result->guest_email);
    }

    public function test_find_chat_by_session_returns_correct_chat()
    {
        // Arrange
        $sessionId = 'chat_test_session_id';
        $expectedChat = new Chat(['session_id' => $sessionId]);

        $this->chatRepository->shouldReceive('findBySessionId')
            ->once()
            ->with($sessionId)
            ->andReturn($expectedChat);

        // Act
        $result = $this->chatService->findChatBySession($sessionId);

        // Assert
        $this->assertInstanceOf(Chat::class, $result);
        $this->assertEquals($sessionId, $result->session_id);
    }

    public function test_assign_agent_to_chat_updates_successfully()
    {
        // Arrange
        $chatId = 1;
        $agentId = 2;

        $this->chatRepository->shouldReceive('assignAgent')
            ->once()
            ->with($chatId, $agentId)
            ->andReturn(true);

        // Act
        $result = $this->chatService->assignAgentToChat($chatId, $agentId);

        // Assert
        $this->assertTrue($result);
    }

    public function test_close_chat_updates_status_successfully()
    {
        // Arrange
        $chatId = 1;

        $this->chatRepository->shouldReceive('updateStatus')
            ->once()
            ->with($chatId, 'closed')
            ->andReturn(true);

        // Act
        $result = $this->chatService->closeChat($chatId);

        // Assert
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
