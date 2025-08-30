<?php
namespace Database\Factories;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $senderType = fake()->randomElement(['user', 'agent', 'guest']);
        $senderData = $this->getSenderData($senderType);

        return [
            'chat_id' => Chat::factory(),
            'sender_id' => $senderData['sender_id'],
            'sender_type' => $senderType,
            'sender_name' => $senderData['sender_name'],
            'message' => fake()->randomElement([
                'مرحباً، أحتاج مساعدة في استخدام المنتج',
                'هل يمكنك مساعدتي في حل هذه المشكلة؟',
                'شكراً لك على المساعدة',
                'أواجه مشكلة في تسجيل الدخول',
                'كيف يمكنني تحديث بياناتي؟',
                'Hello, I need help with my account',
                'Can you help me reset my password?',
                'Thank you for your assistance',
                'I have a technical issue',
                'How can I upgrade my plan?'
            ]),
            'message_type' => fake()->randomElement(['text', 'file', 'image']),
            'is_read' => fake()->boolean(60), // 60% chance of being read
            'sent_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Get sender data based on sender type.
     */
    private function getSenderData(string $senderType): array
    {
        switch ($senderType) {
            case 'agent':
                return [
                    'sender_id' => User::factory()->agent(),
                    'sender_name' => fake()->name(),
                ];
            case 'user':
                return [
                    'sender_id' => User::factory()->customer(),
                    'sender_name' => fake()->name(),
                ];
            case 'guest':
            default:
                return [
                    'sender_id' => null,
                    'sender_name' => fake()->name(),
                ];
        }
    }

    /**
     * Create a message from an agent.
     */
    public function fromAgent(): static
    {
        return $this->state(function (array $attributes) {
            $agent = User::factory()->agent()->create();
            return [
                'sender_id' => $agent->id,
                'sender_type' => 'agent',
                'sender_name' => $agent->name,
                'message' => fake()->randomElement([
                    'مرحباً! كيف يمكنني مساعدتك اليوم؟',
                    'شكراً لتواصلك معنا، سأساعدك في حل مشكلتك',
                    'هل يمكنك توضيح المشكلة أكثر؟',
                    'تم حل مشكلتك بنجاح',
                    'هل تحتاج أي مساعدة إضافية؟'
                ]),
            ];
        });
    }

    /**
     * Create a message from a user.
     */
    public function fromUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->customer()->create();
            return [
                'sender_id' => $user->id,
                'sender_type' => 'user',
                'sender_name' => $user->name,
            ];
        });
    }

    /**
     * Create a message from a guest.
     */
    public function fromGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => null,
            'sender_type' => 'guest',
            'sender_name' => fake()->name(),
        ]);
    }

    /**
     * Create an unread message.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    /**
     * Create a read message.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    /**
     * Create a text message.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'text',
        ]);
    }

    /**
     * Create a file message.
     */
    public function file(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'file',
            'message' => 'Shared a file: ' . fake()->word() . '.pdf',
        ]);
    }

    /**
     * Create an image message.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'image',
            'message' => 'Shared an image: ' . fake()->word() . '.jpg',
        ]);
    }

    /**
     * Create a message sent recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }
}
