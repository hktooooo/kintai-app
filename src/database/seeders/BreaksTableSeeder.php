<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreaksTableSeeder extends Seeder
{
    public function run(): void
    {
        // AttendanceSeeder実行後検索
        foreach (Attendance::all() as $attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start'   => Carbon::parse($attendance->work_date)->setTime(12, 0),
                'break_end'     => Carbon::parse($attendance->work_date)->setTime(13, 0),
                'break_hours'   => "01:00:00",
                'break_seconds' => 3600,
            ]);
        }
    }
}
