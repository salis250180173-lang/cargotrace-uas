<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin CargoTrace',
                'email' => 'admin@cargotrace.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Operator CargoTrace',
                'email' => 'operator@cargotrace.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OPERATOR,
            ],
            [
                'name' => 'Customer CargoTrace',
                'email' => 'customer@cargotrace.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}

