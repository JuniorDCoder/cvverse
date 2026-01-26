<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $admin = User::where('email', 'dcodertechie@gmail.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Admin User',
                'email' => 'dcodertechie@gmail.com',
                'password' => Hash::make('password'), // Default password - should be changed after first login
                'email_verified_at' => now(),
                'role' => 'admin',
                'onboarding_completed' => true,
                'onboarding_completed_at' => now(),
            ]);

            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
