<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧の表示
    public function admin_show_list(Request $request)
    {
        // クエリパラメータ ?month=2025-11 の形式で受け取る
        $monthParam = $request->query('month');

        // 指定がなければ今月
        $current = $monthParam ? Carbon::parse($monthParam) : Carbon::now();

        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        // 日付
        $now = Carbon::now();
        $today = $now->toDateString();

        // 前月と翌月
        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        // 今日の勤怠情報全件取得 *****あとで日付ごとに対応するように直す
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $today)
            ->get();        

        return view('admin.attendance_list', compact('now', 'current', 'prevMonth', 'nextMonth', 'attendances'));
    }

    // スタッフ一覧画面の表示
    public function show_staff_list()
    {
        return view('admin.staff_list');  
    }

   

}
