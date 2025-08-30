<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     */
    public function run(): void
    {
        // Create default admin agent
        User::firstOrCreate(
            ['email' => 'admin@highleveltech.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123!@#'),
                'role' => 'agent',
                'is_online' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create default support agent
        User::firstOrCreate(
            ['email' => 'support@highleveltech.com'],
            [
                'name' => 'Support Agent',
                'password' => Hash::make('support123'),
                'role' => 'agent',
                'is_online' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Production users created successfully!');
        $this->command->warn('Default passwords:');
        $this->command->warn('Admin: admin123!@# | Support: support123');
        $this->command->warn('Please change these passwords after first login!');
    }
}
