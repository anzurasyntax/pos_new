<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'superadmin@example.com';
        $password = 'superadmin123';

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'remember_token' => Str::random(10),
            ]
        );
    }
}

