<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = User::where('role', 'agent')->get();
        $customers = User::where('role', 'user')->get();

        // Create waiting chats (unassigned)
        Chat::factory()->count(5)->waiting()->create();

        // Create guest chats (some waiting, some active)
        Chat::factory()->count(3)->guest()->waiting()->create();
        Chat::factory()->count(4)->guest()->active()->create();

        // Create active chats with agents
        $customers->take(8)->each(function ($customer) use ($agents) {
            Chat::factory()->active()->create([
                'user_id' => $customer->id,
                'agent_id' => $agents->random()->id,
            ]);
        });

        // Create closed chats
        $customers->skip(8)->take(5)->each(function ($customer) use ($agents) {
            Chat::factory()->closed()->create([
                'user_id' => $customer->id,
                'agent_id' => $agents->random()->id,
            ]);
        });

        // Create some guest closed chats
        Chat::factory()->count(3)->guest()->closed()->create();

        $this->command->info('Chats seeded successfully!');
        $this->command->info('Total chats: ' . Chat::count());
        $this->command->info('Waiting chats: ' . Chat::where('status', 'waiting')->count());
        $this->command->info('Active chats: ' . Chat::where('status', 'active')->count());
        $this->command->info('Closed chats: ' . Chat::where('status', 'closed')->count());
        $this->command->info('Guest chats: ' . Chat::whereNotNull('guest_name')->count());
    }
}
