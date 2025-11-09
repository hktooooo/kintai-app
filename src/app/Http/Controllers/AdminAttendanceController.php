<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // 管理者 勤怠一覧の表示
    public function adminShowList(Request $request)
    {
        // クエリパラメータ ?day=2025-01-01 の形式で受け取る
        $dayParam = $request->query('day');

        // 指定がなければ今日
        try {
            $current = $dayParam ? Carbon::parse($dayParam) : Carbon::today();
        } catch (\Exception $e) {
            // 不正な日付が来た場合は今日にフォールバック
            $current = Carbon::today();
        }

        // 検索日と前日と翌日（リンク用）
        $today = $current->copy()->toDateString();
        $prevDay = $current->copy()->subDay()->toDateString();
        $nextDay = $current->copy()->addDay()->toDateString();

        // 今日の勤怠情報を全件取得（ユーザー情報込み）
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $today)
            ->get();

        return view('admin.attendance_list', compact('current', 'prevDay', 'nextDay', 'attendances'));
    }

    // 管理者 勤怠詳細画面の表示
    public function adminShowDetail($id)
    {
        // 勤怠データを1件取得
        $attendance = Attendance::with(['user', 'corrections'])->findOrFail($id);

        // 詳細ページに渡す
        return view('admin.attendance_detail', compact('attendance'));
    }

    // 管理者 勤怠詳細画面から修正
    public function adminDetailCorrection(Request $request)
    {
        $attendance_id = $request->id;

        // 対象の出勤記録を取得
        $attendance = Attendance::findOrFail($attendance_id);

        // 値を更新
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->reason = $request->reason;

        // 勤務時間を計算（時間単位で）
        $start = Carbon::parse($attendance->clock_in);
        $end = Carbon::parse($attendance->clock_out);
        $workingHours = $start->diffInMinutes($end) / 60; // 分→時間に変換

        $attendance->working_hours = round($workingHours, 2);

        // 保存
        $attendance->save();

        return redirect()->back();
    }

    // スタッフ一覧画面の表示
    public function showStaffList()
    {
        return view('admin.staff_list');  
    }



}
