<?php

namespace App\Services;

use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Models\Chat;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ChatService
{
    protected $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function startChatForUser(int $userId): Chat
    {
        $sessionId = $this->generateSessionId();

        return $this->chatRepository->create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'status' => 'waiting'
        ]);
    }

    public function startChatForGuest(string $guestName, string $guestEmail): Chat
    {
        $sessionId = $this->generateSessionId();

        return $this->chatRepository->create([
            'session_id' => $sessionId,
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'status' => 'waiting'
        ]);
    }

    public function findChatBySession(string $sessionId): ?Chat
    {
        return $this->chatRepository->findBySessionId($sessionId);
    }

    public function findChatById(int $chatId): ?Chat
    {
        return $this->chatRepository->findById($chatId);
    }

    public function assignAgentToChat(int $chatId, int $agentId): bool
    {
        return $this->chatRepository->assignAgent($chatId, $agentId);
    }

    public function closeChat(int $chatId): bool
    {
        return $this->chatRepository->updateStatus($chatId, 'closed');
    }

    public function getWaitingChats(): Collection
    {
        return $this->chatRepository->getWaitingChats();
    }

    public function getActiveChats(): Collection
    {
        return $this->chatRepository->getActiveChats();
    }

    public function getAgentActiveChats(int $agentId): Collection
    {
        return $this->chatRepository->getAgentActiveChats($agentId);
    }

    public function getUserChats(int $userId): Collection
    {
        return $this->chatRepository->getUserChats($userId);
    }

    public function getAgentStatistics(int $agentId): array
    {
        return [
            'total_chats' => $this->chatRepository->getAgentTotalChats($agentId),
            'active_chats' => $this->chatRepository->getAgentActiveChats($agentId)->count(),
            'closed_today' => $this->chatRepository->getAgentClosedChatsToday($agentId),
            'average_response_time' => $this->chatRepository->getAgentAverageResponseTime($agentId),
            'satisfaction_rating' => $this->chatRepository->getAgentSatisfactionRating($agentId),
        ];
    }

    private function generateSessionId(): string
    {
        return 'chat_' . Str::uuid();
    }
}
