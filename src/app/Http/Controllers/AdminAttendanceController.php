<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakCorrection;
use App\Models\User;
use App\Http\Requests\CorrectionRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function adminDetailCorrection(CorrectionRequest $request)
    {
        $attendance_id = $request->id;

        // 対象の出勤記録を取得
        $attendance = Attendance::findOrFail($attendance_id);

        // 値を更新
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->reason = $request->reason;

        // 合計の休憩時間計算用
        $totalBreakSeconds = 0;

        // breaks が null の場合は空配列にする
        $breaks = $request->breaks ?? [];

        // 休憩情報を登録
        foreach ($breaks as $breakInput) {
            // break_id が存在する → 既存休憩を更新
            if (!empty($breakInput['break_id'])) {

                $break_time = BreakTime::find($breakInput['break_id']);

                if ($break_time) {
                    // 休憩時間を計算
                    $start = Carbon::parse($breakInput['break_start']);
                    $end = Carbon::parse($breakInput['break_end']);

                    // 秒差
                    $seconds = $start->diffInSeconds($end);
                    $totalBreakSeconds += $seconds;

                    // 時・分・秒に分解
                    //$hours = floor($seconds / 3600);
                    //$minutes = floor(($seconds % 3600) / 60);
                    //$secs = $seconds % 60;

                    // HH:MM:SS に整形
                    //$break_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);

                    $break_time->update([
                        'break_start' => $breakInput['break_start'],
                        'break_end'   => $breakInput['break_end'],
                        //'break_hours' => $break_hours,
                        'break_seconds' => $seconds,
                    ]);
                }

            // break_id が無い → 新規休憩として作成
            } else {
                if (!empty($breakInput['break_start']) && !empty($breakInput['break_end'])) {
                    // 休憩時間を計算
                    $start = Carbon::parse($breakInput['break_start']);
                    $end = Carbon::parse($breakInput['break_end']);

                    // 秒差
                    $seconds = $start->diffInSeconds($end);
                    $totalBreakSeconds += $seconds;

                    // 時・分・秒に分解
                    //$hours = floor($seconds / 3600);
                    //$minutes = floor(($seconds % 3600) / 60);
                    //$secs = $seconds % 60;

                    // HH:MM:SS に整形
                    //$break_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);

                    BreakTime::create([
                        'attendance_id' => $attendance->id, // 必須：どの勤怠に属するか
                        'break_start'   => $breakInput['break_start'],
                        'break_end'     => $breakInput['break_end'],
                        //'break_hours' => $break_hours,
                        'break_seconds' => $seconds,
                    ]);
                }
            }
        }

        // 勤務時間を計算（時間単位で）
        $start = Carbon::parse($request->clock_in);
        $end = Carbon::parse($request->clock_out);

        // 秒差
        $workingSeconds = $start->diffInSeconds($end);

        // 休憩時間を引く
        $workingSeconds -= $totalBreakSeconds;

        // HH:MM:SS に整形
        //$total_hours = floor($totalBreakSeconds / 3600);
        //$total_minutes = floor(($totalBreakSeconds % 3600) / 60);
        //$total_secs = $totalBreakSeconds % 60;

        $attendance->total_break_seconds = $totalBreakSeconds;
      
        //$hours = floor($workingSeconds / 3600);
        //$minutes = floor(($workingSeconds % 3600) / 60);
        //$secs = $workingSeconds % 60;        
        $attendance->working_seconds = $workingSeconds;

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

        // 休憩修正申請データを取得
        $break_time_corrections = BreakCorrection::where('attendance_correction_id', $attendance_correction->id)
            ->get();

        // 承認ページに渡す
        return view('admin.approve_correct_request', compact('attendance_correction', 'break_time_corrections', 'attendance_correct_request_id'));
    }

    // 修正申請承認の実行
    public function approveCorrectRequestExec(Request $request)
    {
        // 修正申請データを取得して承認状態に更新
        $attendance_correction = AttendanceCorrection::findOrFail($request->attendance_correct_request_id);
        $attendance_correction->approval_status = 'approved';
        $attendance_correction->save();

        // 勤怠データを取得して更新
        $attendance = Attendance::findOrFail($attendance_correction->attendance_id);
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->reason = $request->reason;

        // 合計の休憩時間計算用
        $totalBreakSeconds = 0;

        // breaks が null の場合は空配列にする
        $breaks = $request->breaks ?? [];

        // 休憩情報を登録
        foreach ($breaks as $breakInput) {
            // break_start または break_end が空ならスキップ
            if (empty($breakInput['break_start']) || empty($breakInput['break_end'])) {
                continue;
            }

            // 秒差・休憩時間計算用
            $start = Carbon::parse($breakInput['break_start']);
            $end = Carbon::parse($breakInput['break_end']);
            $seconds = $start->diffInSeconds($end);
            $totalBreakSeconds += $seconds;

            //$hours = floor($seconds / 3600);
            //$minutes = floor(($seconds % 3600) / 60);
            //$secs = $seconds % 60;
            //$break_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);

            // break_id が存在する → 既存休憩を更新
            if (!empty($breakInput['break_id'])) {
                $break_time = BreakTime::find($breakInput['break_id']);
                if ($break_time) {
                    $break_time->update([
                        'break_start' => $breakInput['break_start'],
                        'break_end'   => $breakInput['break_end'],
                        //'break_hours' => $break_hours,
                        'break_seconds' => $seconds,
                    ]);
                }

            // break_id が無い → 新規休憩として作成
            } else {
                // 休憩時間を計算
                BreakTime::create([
                    'attendance_id' => $attendance->id, // 必須：どの勤怠に属するか
                    'break_start'   => $breakInput['break_start'],
                    'break_end'     => $breakInput['break_end'],
                    //'break_hours' => $break_hours,
                    'break_seconds' => $seconds,
                ]);
            }
        }

        // 勤務時間を計算（時間単位で）
        $start = Carbon::parse($request->clock_in);
        $end = Carbon::parse($request->clock_out);
        // 秒差
        $workingSeconds = $start->diffInSeconds($end);
        // 休憩時間を引く
        $workingSeconds -= $totalBreakSeconds;

        // 勤務時間と休憩時間を登録
        $attendance->total_break_seconds = $totalBreakSeconds;
        $attendance->working_seconds = $workingSeconds;

        // 出席を保存
        $attendance->save();

        return redirect()->back();
    }

    public function exportAttendanceCsv($id, Request $request)
    {
        // $idで受け取ったユーザーID
        $userId = $id;

        // クエリパラメータ ?month=2025-11 の形式で受け取る
        $monthParam = $request->query('month', date('Y-m'));    // デフォルト今月
        $current = Carbon::parse($monthParam . '-01');

        $fileName = 'attendance_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($userId, $current) {

            $handle = fopen('php://output', 'w');

            // Excel で文字化けしないように Shift-JIS へ変換
            $header = [
                '氏名',
                '日付',
                '出勤時間',
                '退勤時間',
                '休憩時間',
                '勤務時間合計',
                '備考'
            ];

            // Shift-JIS に変換して出力
            fputcsv($handle, mb_convert_encoding($header, 'SJIS-win', 'UTF-8'));

            // Attendance を chunk で取得しながら出力
            Attendance::with('user')
                ->where('user_id', $userId)
                ->whereYear('work_date', $current->year)
                ->whereMonth('work_date', $current->month)
                ->orderBy('work_date')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {

                        // ---- ユーザ名取得 ----
                        $userName = $row->user?->name ?? '未設定';

                        // ---- 日付の変換 ----
                        $workDate = $row->work_date?->format('Y-m-d');

                        // ---- 時間の変換 ----
                        $clockIn  = $row->clock_in?->format('H:i');
                        $clockOut = $row->clock_out?->format('H:i');
                        $totalBreak   = $row->total_break_hi;
                        $workingHours = $row->working_hours_hi;
                    
                        // CSV行
                        $csvRow = [
                            $userName,
                            $workDate,       // 変換後の値
                            $clockIn,        // 変換後の値
                            $clockOut,       // 変換後の値
                            $totalBreak,     // 変換後の値
                            $workingHours,   // 変換後の値
                            $row->reason,
                        ];

                        // Shift-JIS に変換
                        $encodedRow = array_map(function ($v) {
                            return mb_convert_encoding($v, 'SJIS-win', 'UTF-8');
                        }, $csvRow);

                        fputcsv($handle, $encodedRow);
                    }
                });

            fclose($handle);
        });

        // ダウンロードヘッダー
        $response->headers->set('Content-Type', 'text/csv; charset=Shift_JIS');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
