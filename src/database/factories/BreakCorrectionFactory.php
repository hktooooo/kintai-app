<?php

namespace Database\Factories;

use App\Models\BreakCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakCorrectionFactory extends Factory
{
    protected $model = BreakCorrection::class;

    public function definition()
    {
        return [
            'attendance_correction_id' => 1,
            'break_id' => null,
            'break_start_correction' => $this->faker->time('H:i:s'),
            'break_end_correction' => $this->faker->time('H:i:s'),
        ];
    }
}