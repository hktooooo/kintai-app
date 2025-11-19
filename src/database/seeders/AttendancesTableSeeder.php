<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        // 開始日
        $start = Carbon::parse('2025-11-01');

        // 終了日
        $end = Carbon::parse('2025-11-30');

        // 日付ループ
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            // 土日を除外（0:日, 6:土）
            if ($date->isWeekend()) {
                continue;
            }

            Attendance::create([
                'user_id'  => 1,
                'work_date'     => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out'=> $date->copy()->setTime(18, 0),
                'working_hours' => "08:00:00",
                'total_break' => "01:00:00",
                'status' => 'completed'
            ]);
        }
    }
}
