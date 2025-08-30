<?php

namespace App\Repositories\Contracts;

use App\Models\Chat;
use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    public function create(array $data): Chat;
    public function findBySessionId(string $sessionId): ?Chat;
    public function findById(int $id): ?Chat;
    public function updateStatus(int $chatId, string $status): bool;
    public function assignAgent(int $chatId, int $agentId): bool;
    public function getActiveChats(): Collection;
    public function getWaitingChats(): Collection;
    public function getAgentActiveChats(int $agentId): Collection;
    public function getUserChats(int $userId): Collection;
    public function getAgentTotalChats(int $agentId): int;
    public function getAgentClosedChatsToday(int $agentId): int;
    public function getAgentAverageResponseTime(int $agentId): float;
    public function getAgentSatisfactionRating(int $agentId): float;
}
