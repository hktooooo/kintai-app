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
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'working_seconds' => 8 * 60 * 60,
                'total_break_seconds' => 1 * 60 * 60,
                'status' => 'completed'
            ]);
        }

        // 11/3のみ複数ユーザー
        $date = Carbon::parse('2025-11-03');

        // ユーザループ
        for ($i = 2; $i <= 6; $i++) {
            Attendance::create([
                'user_id'  => $i,
                'work_date'     => $date->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'working_seconds' => 8 * 60 * 60,
                'total_break_seconds' => 1 * 60 * 60,
                'status' => 'completed'
            ]);
        }
    }
}
