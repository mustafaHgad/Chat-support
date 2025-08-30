<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chats = Chat::with(['user', 'agent'])->get();

        foreach ($chats as $chat) {
            $messageCount = rand(3, 15); // Random number of messages per chat

            for ($i = 0; $i < $messageCount; $i++) {
                $this->createMessageForChat($chat, $i);
            }
        }

        $this->command->info('Messages seeded successfully!');
        $this->command->info('Total messages: ' . Message::count());
        $this->command->info('User messages: ' . Message::where('sender_type', 'user')->count());
        $this->command->info('Agent messages: ' . Message::where('sender_type', 'agent')->count());
        $this->command->info('Guest messages: ' . Message::where('sender_type', 'guest')->count());
    }

    private function createMessageForChat(Chat $chat, int $index)
    {
        $senderType = $this->determineSenderType($chat, $index);
        $messageData = $this->getMessageData($chat, $senderType, $index);

        Message::factory()->create($messageData);
    }

    private function determineSenderType(Chat $chat, int $index): string
    {
        // First message is always from customer/guest
        if ($index === 0) {
            return $chat->user_id ? 'user' : 'guest';
        }

        // If chat has agent, alternate between customer and agent
        if ($chat->agent_id) {
            return ($index % 2 === 0) ? ($chat->user_id ? 'user' : 'guest') : 'agent';
        }

        // If no agent assigned, all messages are from customer/guest
        return $chat->user_id ? 'user' : 'guest';
    }

    private function getMessageData(Chat $chat, string $senderType, int $index): array
    {
        $baseTime = $chat->created_at->addMinutes($index * 2);

        switch ($senderType) {
            case 'user':
                return [
                    'chat_id' => $chat->id,
                    'sender_id' => $chat->user_id,
                    'sender_type' => 'user',
                    'sender_name' => $chat->user->name,
                    'message' => $this->getUserMessage($index),
                    'sent_at' => $baseTime,
                    'is_read' => rand(0, 1),
                ];

            case 'agent':
                return [
                    'chat_id' => $chat->id,
                    'sender_id' => $chat->agent_id,
                    'sender_type' => 'agent',
                    'sender_name' => $chat->agent->name,
                    'message' => $this->getAgentMessage($index),
                    'sent_at' => $baseTime,
                    'is_read' => rand(0, 1),
                ];

            case 'guest':
            default:
                return [
                    'chat_id' => $chat->id,
                    'sender_id' => null,
                    'sender_type' => 'guest',
                    'sender_name' => $chat->guest_name,
                    'message' => $this->getGuestMessage($index),
                    'sent_at' => $baseTime,
                    'is_read' => rand(0, 1),
                ];
        }
    }

    private function getUserMessage(int $index): string
    {
        $messages = [
            'Hello, I need help using the product.',
            'I\'m having trouble logging in.',
            'How can I update my personal information?',
            'Can you help me set up my account?',
            'I want to learn more about the available services.',
            'Thank you for your prompt assistance.',
            'Can I activate this feature on my account?',
            'I need urgent technical support.'
        ];

        return $messages[$index % count($messages)];
    }

    private function getAgentMessage(int $index): string
    {
        $messages = ['Hello! How can I help you today?',
            'Thank you for contacting us. I will help you resolve this issue.',
            'Can you explain the issue in more detail?',
            'The issue was resolved successfully. Do you need any further assistance?',
            'I will transfer you to the appropriate department.',
            'Your information was successfully updated.',
            'Any further questions?',
            'Thank you. We hope we have been helpful.',
        ];

        return $messages[$index % count($messages)];
    }

    private function getGuestMessage(int $index): string
    {
        $messages = [
            'Hello, I need help with your product',
            'I am having trouble logging in',
            'Can you help me reset my password?',
            'How do I upgrade my subscription?',
            'I have a billing question',
            'Thank you for your help',
            'Is there a way to contact sales?',
            'I need technical support',
        ];

        return $messages[$index % count($messages)];
    }
}
