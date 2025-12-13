<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreaksTableSeeder extends Seeder
{
    public function run(): void
    {
        // AttendanceSeeder実行後検索
        foreach (Attendance::all() as $attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start'   => '12:00:00',
                'break_end'     => '13:00:00',
                'break_seconds' => 1 * 60 * 60,
            ]);
        }
    }
}
