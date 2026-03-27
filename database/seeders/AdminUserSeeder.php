<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@example.com';
        $password = 'admin123';

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'role' => 'manager',
                'remember_token' => Str::random(10),
            ]
        );
    }
}

