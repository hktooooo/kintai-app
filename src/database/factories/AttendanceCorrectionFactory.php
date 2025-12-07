<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'attendance_id' => 1,
            'clock_in_correction' => $this->faker->time('H:i:s'),
            'clock_out_correction' => $this->faker->time('H:i:s'),
            'reason_correction' => $this->faker->sentence(),
            'approval_status' => 'pending',   // 初期値を pending に
            'requested_date' => now()->toDateString(),
        ];
    }
}
