<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()
            ->dateTimeBetween('-30 days', 'now')
            ->format('Y-m-d');

        $clockIn = fake()->dateTimeBetween(
            $date.' 08:45:00',
            $date.' 09:45:00'
        );

        $clockOut = fake()->dateTimeBetween(
            $date.' 17:30:00',
            $date.' 18:45:00'
        );

        $workMinutes = max(
            0,
            (int) (
                ($clockOut->getTimestamp() - $clockIn->getTimestamp()) / 60
            )
        );

        $lateLimit = strtotime($date.' 09:15:00');

        $lateMinutes = $clockIn->getTimestamp() > $lateLimit
            ? (int) (
                ($clockIn->getTimestamp() - $lateLimit) / 60
            )
            : 0;

        return [
            'user_id' => User::factory(),
            'attendance_date' => $date,
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'work_minutes' => $workMinutes,
            'late_minutes' => $lateMinutes,
            'status' => $workMinutes < 240
                ? 'half_day'
                : ($lateMinutes > 0 ? 'late' : 'present'),
            'source' => 'web',
            'is_auto_closed' => false,
        ];
    }
}
