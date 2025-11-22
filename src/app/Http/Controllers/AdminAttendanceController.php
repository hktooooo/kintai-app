<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\User;
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
        $break_times = BreakTime::where('attendance_id', $id)->get();

        // 詳細ページに渡す
        return view('admin.attendance_detail', compact('attendance', 'break_times'));
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
        $users = User::all();
        return view('admin.staff_list', compact('users'));  
    }

    // スタッフ別勤怠一覧画面の表示
    public function showAttendanceStaffList($id, Request $request)
    {
        // $idで受け取ったユーザーID
        $userId = $id;

        // ユーザー情報
        $user = User::findOrFail($userId);

        // クエリパラメータ ?month=2025-11 の形式で受け取る
        $monthParam = $request->query('month');

        // 指定がなければ今月
        $current = $monthParam ? Carbon::parse($monthParam) : Carbon::now();

        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        // 日付を配列に追加
        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->copy();
        }

        // 前月と翌月
        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        // 指定した月で取得
        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereYear('work_date', $current->year)
            ->whereMonth('work_date', $current->month)
            ->get();

        $attendancesByDate = [];

        foreach ($dates as $date) {
            $attendanceForDate = $attendances->first(function ($att) use ($date) {
                return Carbon::parse($att->work_date)->isSameDay($date);
            });
            $attendancesByDate[$date->toDateString()] = $attendanceForDate;
        }

        return view('admin.attendance_staff_list', compact('user', 'attendancesByDate', 'dates', 'current', 'prevMonth', 'nextMonth'));
    }

    // 修正申請承認画面の表示
    public function approveCorrectRequest($attendance_correct_request_id)
    {
        // 修正申請データを取得
        $attendance_correction = AttendanceCorrection::where('id', $attendance_correct_request_id)->firstOrFail();
        $attendance_id = $attendance_correction -> attendance_id;

        // 勤怠データを取得
        $attendance = Attendance::with(['user', 'corrections'])
            ->findOrFail($attendance_id);
        $break_times = BreakTime::where('attendance_id', $attendance_id)
            ->get();

        // 承認ページに渡す
        return view('admin.approve_correct_request', compact('attendance', 'break_times', 'attendance_correct_request_id'));
    }

    // 修正申請承認の実行
    public function approveCorrectRequestExec(Request $request)
    {
        // 修正申請データを取得
        $attendance_correct_request_id = $request->attendance_correct_request_id;
        $attendance_correction = AttendanceCorrection::where('id', $attendance_correct_request_id)->firstOrFail();

        $attendance_correction->approval_status = 'approved'; 
        $attendance_correction->save();

        return redirect()->back();
    }    
}
