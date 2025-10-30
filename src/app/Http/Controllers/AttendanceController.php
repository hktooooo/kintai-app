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
}
