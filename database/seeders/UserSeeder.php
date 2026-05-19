<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'CEO User', 'email' => 'ceo@ddhrm.local', 'role' => 'ceo'],
            ['name' => 'Admin User', 'email' => 'admin@ddhrm.local', 'role' => 'admin'],
            ['name' => 'HR Admin', 'email' => 'hr@ddhrm.local', 'role' => 'hr'],
            ['name' => 'Demo Staff', 'email' => 'staff@ddhrm.local', 'role' => 'staff'],
        ];

        foreach ($users as $row) {
            $role = Role::query()->where('slug', $row['role'])->firstOrFail();

            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
            );

            $user->roles()->sync([$role->id]);
        }
    }
}
