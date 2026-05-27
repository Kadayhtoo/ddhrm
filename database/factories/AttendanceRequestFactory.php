<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRequestFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d');

        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'requested_by' => User::factory(),
            'type' => 'full_day',
            'requested_date' => $date,
            'requested_clock_in_at' => $date.' 09:00:00',
            'requested_clock_out_at' => $date.' 18:30:00',
            'reason' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
