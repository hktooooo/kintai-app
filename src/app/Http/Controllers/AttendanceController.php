<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
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
        $workingHours = $start->diffInMinutes($end) / 60; // 分→時間に変換

        $attendance->working_hours = round($workingHours, 2);

        // 保存
        $attendance->save(); 

        return back();
    }


    // 勤怠一覧の表示
    public function show_list(Request $request)
    {
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

        return view('attendance_list', compact('dates', 'current', 'prevMonth', 'nextMonth'));
    }

    // 申請一覧画面の表示
    public function show_stamp_list()
    {

        return view('stamp_correction_request_list');  
    }

    // 勤怠詳細画面の表示
    public function show_detail(Request $request)
    {
        $id = $request->query('id');
        $id = empty($id) ? 0 : $id; 
        return view('attendance_detail', compact('id'));  
    }

    

}
