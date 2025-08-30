<?php

namespace App\Repositories\Eloquent;

use App\Models\Chat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Support\Collection;

class ChatRepository implements ChatRepositoryInterface
{
    protected $model;

    public function __construct(Chat $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Chat
    {
        return $this->model->create($data);
    }

    public function findBySessionId(string $sessionId): ?Chat
    {
        return $this->model->where('session_id', $sessionId)->first();
    }

    public function findById(int $id): ?Chat
    {
        return $this->model->with(['messages', 'user', 'agent'])->find($id);
    }

    public function updateStatus(int $chatId, string $status): bool
    {
        $updateData = ['status' => $status];

        if ($status === 'closed') {
            $updateData['closed_at'] = now();
        }

        return $this->model->where('id', $chatId)->update($updateData) > 0;
    }

    public function assignAgent(int $chatId, int $agentId): bool
    {
        return $this->model->where('id', $chatId)->update([
                'agent_id' => $agentId,
                'status' => 'active',
                'started_at' => now()
            ]) > 0;
    }

    public function getActiveChats(): Collection
    {
        return $this->model->where('status', 'active')
            ->with(['user', 'agent', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getWaitingChats(): Collection
    {
        return $this->model->where('status', 'waiting')
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getAgentActiveChats(int $agentId): Collection
    {
        return $this->model->where('agent_id', $agentId)
            ->where('status', 'active')
            ->with(['user', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getUserChats(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['agent', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getAgentTotalChats(int $agentId): int
    {
        return $this->model->where('agent_id', $agentId)->count();
    }

    public function getAgentClosedChatsToday(int $agentId): int
    {
        return $this->model->where('agent_id', $agentId)
            ->where('status', 'closed')
            ->whereDate('closed_at', today())
            ->count();
    }

    public function getAgentAverageResponseTime(int $agentId): float
    {
        // This would require more complex calculation
        // For now, return a mock value
        return 5.2; // minutes
    }

    public function getAgentSatisfactionRating(int $agentId): float
    {
        // This would integrate with a rating system
        // For now, return a mock value
        return 4.7; // out of 5
    }
}
