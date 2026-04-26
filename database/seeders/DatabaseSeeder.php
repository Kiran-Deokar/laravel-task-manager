<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // -----------------------------------
        // 1. Create Users
        // -----------------------------------
        
        // Admin User
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@task-manager.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // User 1
        $user1 = User::create([
            'name' => 'Demo User 1',
            'email' => 'demo1@task-manager.com',
            'password' => Hash::make('demo123'),
            'role' => 'user',
        ]);

        // User 2
        $user2 = User::create([
            'name' => 'Demo User 2',
            'email' => 'demo2@task-manager.com',
            'password' => Hash::make('demo123'),
            'role' => 'user',
        ]);

    }
}