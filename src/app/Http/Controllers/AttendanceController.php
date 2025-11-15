<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 出勤登録画面の表示
    public function showMain()
    {
        // 現在ログインしているユーザーIDを取得
        $userId = Auth::id();

        // 日付情報
        $now = Carbon::now();
        $today = $now->toDateString();
        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$now->dayOfWeek];     // 曜日を日本語に変換
        
        // 今日のレコードを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today) // 今日のレコード
            ->first();
 
        if (!$attendance) {
            $status = 'absent';
        }
        else {
            $status = $attendance->status;
        }

        return view('attendance', compact('status', 'now', 'weekday'));  
    }

    // 出勤登録
    public function clockIn(Request $request)
    {
        // 現在ログインしているユーザーID
        $userId = Auth::id();

        // 今日の日付と現在時刻
        $now = Carbon::now();
        $today = $now->toDateString();

        // 既に今日の出勤記録があるかチェック
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance) {
            return back()->with('error', '本日はすでに出勤済みです。');
        }

        // 出勤情報を新規登録
        Attendance::create([
            'user_id' => $userId,
            'work_date' => $today,
            'clock_in' => $now->format('H:i:s'),
            'status' => 'working',
        ]);

        return back();
    }
    
    // 退勤登録
    public function clockOut(Request $request)
    {
        // 現在ログインしているユーザーID
        $userId = Auth::id();

        // 今日の日付と現在時刻
        $now = Carbon::now();
        $today = $now->toDateString();

        // 今日の出勤記録を取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();

        // 出勤記録が存在しない場合
        if (!$attendance) {
            return back()->with('error', 'エラー');
        }

        // すでに退勤済みの場合はエラー
        if ($attendance->clock_out) {
            return back()->with('error', 'エラー');
        }

        // 退勤情報を登録
        $attendance->clock_out = $now->format('H:i:s');
        $attendance->status = 'completed';

        // 勤務時間を計算（時間単位で）
        $start = Carbon::parse($attendance->clock_in);
        $end = Carbon::parse($attendance->clock_out);
        
        // 秒差
        $seconds = $start->diffInSeconds($end);

        // 休憩時間の合計を取得
        $total_break = $attendance->total_break; // HH:MM:SS 文字列

        // 秒に変換
        if ($total_break) {
            $parts = explode(':', $total_break);
            $total_break_seconds = ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        } else {
            $total_break_seconds = 0;
        }

        $seconds -= $total_break_seconds;

        // 時・分・秒に分解
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        // HH:MM:SS に整形
        $attendance->working_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);

        // 保存
        $attendance->save(); 

        return back();
    }

    // 休憩開始登録
    public function breakStart(Request $request)
    {
        // 現在ログインしているユーザーID
        $userId = Auth::id();

        // 今日の日付と現在時刻
        $now = Carbon::now();
        $today = $now->toDateString();

        // 今日の出勤記録を取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();
        
        $attendance_id = $attendance -> id;

        // 休憩開始情報を新規登録
        BreakTime::create([
            'user_id' => $userId,
            'attendance_id' => $attendance_id,
            'break_start' => $now->format('H:i:s'),
        ]);

        // 状態を保存 
        $attendance->status = 'break';  
        $attendance->save();

        return back();
    }

    // 休憩終了登録
    public function breakEnd(Request $request)
    {
        // 現在ログインしているユーザーID
        $userId = Auth::id();

        // 今日の日付と現在時刻
        $now = Carbon::now();
        $today = $now->toDateString();

        // 今日の出勤記録を取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('work_date', $today)
            ->first();

        // 今日の休憩記録を取得     
        $break_time = $attendance?->breaks()->latest()->first();

        // 休憩情報を登録
        $break_time->break_end = $now->format('H:i:s');
        $attendance->status = 'working';

        // 休憩時間を計算
        $start = Carbon::parse($break_time->break_start);
        $end = Carbon::parse($break_time->break_end);

        // 秒差
        $seconds = $start->diffInSeconds($end);

        // 時・分・秒に分解
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        // HH:MM:SS に整形
        $break_time->break_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);

        // トータル時間を更新
        $break_time->break_seconds = $seconds;

        // 休憩を保存   
        $break_time->save();

        $totalBreakSeconds = $attendance->breaks->sum('break_seconds');

        // 時・分・秒に分解
        $total_hours = floor($totalBreakSeconds / 3600);
        $total_minutes = floor(($totalBreakSeconds % 3600) / 60);
        $total_secs = $totalBreakSeconds % 60;

        $attendance->total_break = sprintf('%02d:%02d:%02d', $total_hours, $total_minutes, $total_secs);

        // 出席を保存
        $attendance->save(); 

        return back();
    }
    

    // 勤怠一覧の表示
    public function show_list(Request $request)
    {
        // 現在ログインしているユーザーID
        $userId = Auth::id();

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

        return view('attendance_list', compact('attendances', 'dates', 'current', 'prevMonth', 'nextMonth'));
    }

    // 勤怠詳細画面の表示
    public function showDetail($id)
    {
        // 勤怠データを1件取得
        $attendance = Attendance::with(['user', 'corrections'])->findOrFail($id);
        $break_times = BreakTime::where('attendance_id', $id)->get();

        // 詳細ページに渡す
        return view('attendance_detail', compact('attendance', 'break_times'));
    }

    // 勤怠詳細画面から修正を申請
    public function submitDetailCorrection(Request $request)
    {
        // 現在ログインしているユーザーID
        $userId = Auth::id();

        // 現在日付
        $today = Carbon::now()->toDateString();

        $attendance_id = $request->id;

        // 修正情報を新規登録
        AttendanceCorrection::create([
            'user_id' => $userId,
            'attendance_id' => $attendance_id,
            'requested_date' => $today,
            'approval_status' => 'pending',
        ]);

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

    // 申請一覧画面の表示
    public function showStampList(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        // 共通クエリを読み込み
        $query = AttendanceCorrection::with(['user', 'attendance']);

        // 権限で分岐
        if (Auth::guard('admin')->check()) {
            // 管理者は絞り込みなし（全件）
        } else {
            // 一般ユーザーは自分のデータのみ
            $query->where('user_id', Auth::id());
        }
        
        // 承認ステータスで共通分岐
        if ($tab === 'pending') {
            $query->where('approval_status', 'pending');
        } else {
            $query->where('approval_status', 'approved');
        }

        // 共通の並び順
        $corrections = $query->orderBy('requested_date', 'desc')->get();

        return view('stamp_correction_request_list', compact('corrections', 'tab'));
    }
}
