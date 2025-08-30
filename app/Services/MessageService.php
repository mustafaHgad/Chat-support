<?php

namespace App\Services;

use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Exception;

class MessageService
{
    protected $messageRepository;
    protected $chatRepository;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        ChatRepositoryInterface $chatRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->chatRepository = $chatRepository;
    }

    public function sendMessage(array $data)
    {
        // Validate chat exists
        $chat = $this->chatRepository->findById($data['chat_id']);
        if (!$chat) {
            throw new Exception('Chat not found');
        }

        return $this->messageRepository->create([
            'chat_id' => $data['chat_id'],
            'sender_id' => $data['sender_id'] ?? null,
            'sender_type' => $data['sender_type'],
            'sender_name' => $data['sender_name'],
            'message' => $data['message'],
            'message_type' => $data['message_type'] ?? 'text',
            'sent_at' => now()
        ]);
    }

    public function getChatMessages(int $chatId)
    {
        return $this->messageRepository->getByChatId($chatId);
    }

    public function markMessageAsRead(int $messageId)
    {
        return $this->messageRepository->markAsRead($messageId);
    }
}
