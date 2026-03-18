<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'name'              => 'Hotel Admin',
                'password'          => Hash::make('admin@123'),
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
