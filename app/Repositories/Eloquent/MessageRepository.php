<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    protected $model;

    public function __construct(Message $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    public function getByChatId(int $chatId): Collection
    {
        return $this->model->where('chat_id', $chatId)
            ->with('sender')
            ->orderBy('sent_at', 'asc')
            ->get();
    }

    public function markAsRead(int $messageId): bool
    {
        return $this->model->where('id', $messageId)
                ->update(['is_read' => true]) > 0;
    }

    public function getUnreadCount(int $chatId): int
    {
        return $this->model->where('chat_id', $chatId)
            ->where('is_read', false)
            ->count();
    }

    public function getUnreadMessages(int $chatId): Collection
    {
        return $this->model->where('chat_id', $chatId)
            ->where('is_read', false)
            ->orderBy('sent_at', 'asc')
            ->get();
    }

    public function markChatMessagesAsRead(int $chatId, int $userId = null): bool
    {
        $query = $this->model->where('chat_id', $chatId)
            ->where('is_read', false);

        if ($userId) {
            $query->where('sender_id', '!=', $userId);
        }

        return $query->update(['is_read' => true]) >= 0;
    }

    public function getMessagesBetweenDates(int $chatId, string $startDate, string $endDate): Collection
    {
        return $this->model->where('chat_id', $chatId)
            ->whereBetween('sent_at', [$startDate, $endDate])
            ->orderBy('sent_at', 'asc')
            ->get();
    }
}
