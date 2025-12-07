<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'work_date' => $this->faker->date(),
            'clock_in' => $this->faker->time(),
            'clock_out' => $this->faker->time(),
            'status' => 'working',
        ];
    }
}
