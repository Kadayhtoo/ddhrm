<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->where('is_active', true)
            ->limit(10)
            ->get()
            ->each(function (User $user) {
                Attendance::factory()
                    ->count(5)
                    ->state(fn () => ['user_id' => $user->id])
                    ->create();
            });
    }
}
