<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isGuest = fake()->boolean(40); // 40% chance of guest chat

        return [
            'session_id' => 'chat_' . Str::uuid(),
            'user_id' => $isGuest ? null : User::factory()->customer(),
            'agent_id' => fake()->boolean(70) ? User::factory()->agent() : null, // 70% assigned to agent
            'guest_name' => $isGuest ? fake()->name() : null,
            'guest_email' => $isGuest ? fake()->safeEmail() : null,
            'status' => fake()->randomElement(['waiting', 'active', 'closed']),
            'started_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'closed_at' => fake()->boolean(30) ? fake()->dateTimeBetween('-1 day', 'now') : null,
        ];
    }

    /**
     * Create a chat for a guest user.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
        ]);
    }

    /**
     * Create a chat for a registered user.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->customer(),
            'guest_name' => null,
            'guest_email' => null,
        ]);
    }

    /**
     * Create a waiting chat.
     */
    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waiting',
            'agent_id' => null,
            'started_at' => null,
        ]);
    }

    /**
     * Create an active chat with agent.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'agent_id' => User::factory()->agent(),
            'started_at' => fake()->dateTimeBetween('-2 hours', 'now'),
        ]);
    }

    /**
     * Create a closed chat.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'agent_id' => User::factory()->agent(),
            'started_at' => fake()->dateTimeBetween('-1 week', '-1 hour'),
            'closed_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Create a chat with messages.
     */
    public function withMessages(int $count = 5): static
    {
        return $this->has(
            Message::factory()->count($count),
            'messages'
        );
    }
}
