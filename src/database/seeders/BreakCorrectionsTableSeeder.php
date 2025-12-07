<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakCorrection;
use Carbon\Carbon;

class BreakCorrectionsTableSeeder extends Seeder
{
    public function run(): void
    {
        // attendance を eager load（N+1 防止）
        $corrections = AttendanceCorrection::with('attendance')->get();

        foreach ($corrections as $attendance_correction) {

            // 元の休憩（1件だけと仮定）
            $break = BreakTime::where('attendance_id', $attendance_correction->attendance_id)->first();

            if (!$break) {
                continue; // 休憩なしならスキップ
            }

            BreakCorrection::create([
                'attendance_correction_id' => $attendance_correction->id,
                'break_id'                 => $break->id,
                'break_start_correction'   => Carbon::parse($attendance_correction->attendance->work_date)->setTime(12, 0),
                'break_end_correction'     => Carbon::parse($attendance_correction->attendance->work_date)->setTime(13, 0),
            ]);
        }
    }
}
