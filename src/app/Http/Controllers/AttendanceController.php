<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 出勤登録画面の表示
    public function show_main()
    {
        $status = 0;

        $now = Carbon::now();
        
        // 曜日を日本語に変換
        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$now->dayOfWeek];

        return view('attendance', [
            'status' => $status,
            'now' => $now,
            'weekday' => $weekday,
        ]);  
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
