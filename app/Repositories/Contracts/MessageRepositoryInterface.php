<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Support\Collection;
interface MessageRepositoryInterface
{
    public function create(array $data): Message;
    public function getByChatId(int $chatId): Collection;
    public function markAsRead(int $messageId): bool;
    public function getUnreadCount(int $chatId): int;
    public function getUnreadMessages(int $chatId): Collection;
    public function markChatMessagesAsRead(int $chatId, int $userId = null): bool;
    public function getMessagesBetweenDates(int $chatId, string $startDate, string $endDate): Collection;
}
