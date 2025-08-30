<?php


namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin agent
        User::factory()->create([
            'name' => 'Admin Agent',
            'email' => 'admin@highleveltech.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'is_online' => true,
            'email_verified_at' => now(),
        ]);

        // Create support agents
        User::factory()->create([
            'name' => 'سارة أحمد',
            'email' => 'sara@highleveltech.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'is_online' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'محمد علي',
            'email' => 'mohamed@highleveltech.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'is_online' => false,
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'فاطمة حسن',
            'email' => 'fatima@highleveltech.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'is_online' => true,
            'email_verified_at' => now(),
        ]);

        // Create test customers
        User::factory()->create([
            'name' => 'أحمد محمد',
            'email' => 'ahmed.customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_online' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'مريم خالد',
            'email' => 'mariam@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_online' => false,
            'email_verified_at' => now(),
        ]);

        // Create additional random agents and customers
        User::factory()->agent()->count(5)->create();
        User::factory()->customer()->count(10)->create();

        $this->command->info('Users seeded successfully!');
        $this->command->info('Agents created: ' . User::where('role', 'agent')->count());
        $this->command->info('Customers created: ' . User::where('role', 'user')->count());
    }
}
