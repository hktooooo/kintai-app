<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceCorrectionsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 開始日
        $start = Carbon::parse('2025-11-03');

        // 終了日
        $end = Carbon::parse('2025-11-13');

        // 日付ループ
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            if ($date->isWeekend()) continue;

            // ① 対象日付の Attendance を取得
            $attendance = Attendance::where('work_date', $date->toDateString())
                ->where('user_id', 1) // ←対象ユーザー
                ->first();

            // ② Attendance がない場合はスキップ（安全）
            if (!$attendance) continue;

            // ③ 取得した Attendance の id を外部キーとしてセット
            AttendanceCorrection::create([
                'user_id'        => 1,
                'attendance_id'  => $attendance->id,
                'clock_in_correction' => $date->copy()->setTime(9, 0),
                'clock_out_correction'=> $date->copy()->setTime(18, 0),
                'requested_date' => Carbon::parse('2025-11-14'),
                'approval_status' => 'pending',
                'reason_correction' => '電車遅延のため'
            ]);
        }

        // 11/3のみ複数ユーザー
        $date = Carbon::parse('2025-11-03');

        // ユーザループ
        for ($i = 2; $i <= 3; $i++) {

            // ① 対象日付の Attendance を取得
            $attendance = Attendance::where('work_date', $date->toDateString())
                ->where('user_id', $i) // ←対象ユーザー
                ->first();

            // ② Attendance がない場合はスキップ（安全）
            if (!$attendance) continue;

            // ③ 取得した Attendance の id を外部キーとしてセット
            AttendanceCorrection::create([
                'user_id'        => $i,
                'attendance_id'  => $attendance->id,
                'clock_in_correction' => $date->copy()->setTime(9, 0),
                'clock_out_correction'=> $date->copy()->setTime(18, 0),
                'requested_date' => Carbon::parse('2025-11-14'),
                'approval_status' => 'pending',
                'reason_correction' => '電車遅延のため'
            ]);
        }
    }
}
