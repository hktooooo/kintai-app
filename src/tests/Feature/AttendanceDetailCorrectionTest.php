<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDetailCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 修正申請 出勤時間が退勤時間より後になっている
    public function test_clock_in_validation()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // 修正申請 休憩開始時間が退勤時間より後になっている
    public function test_break_start_validation()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '20:00', 'break_end' => '21:00'], // 不正値
            ]
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です',
        ]);
    }

    // 修正申請 休憩終了時間が退勤時間より後になっている
    public function test_break_end_validation()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '21:00'], // 不正値
            ]
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }
    
    // 修正申請 備考欄が未入力
    public function test_reason_validation()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ]
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }
    
    // 修正申請処理が実行される
    public function test_correction_exec()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // 管理者登録
        $admin = Admin::factory()->create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('12345678'),
        ]);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'reason' => '修正テスト'
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // ① AttendanceCorrection に登録されたか
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト',
        ]);

        $attendance_correction = AttendanceCorrection::first();

        // ② BreakCorrection に登録されたか
        $this->assertDatabaseHas('break_corrections', [
            'attendance_correction_id' => $attendance_correction->id,
            'break_start_correction' => '12:00:00',
            'break_end_correction' => '13:00:00',
        ]);

        // ステータスは 302 で OK
        $responsePost->assertStatus(302);

        // 管理者ログイン
        $this->actingAs($admin, 'admin');

        // 申請一覧をリクエスト
        $responseList = $this->get("/stamp_correction_request/list");
        $this->assertAuthenticatedAs($admin, guard: 'admin');

        $responseList->assertSeeInOrder([
            '承認待ち',
            '山田 太郎',
            '2025/11/01',
            '修正テスト',
            '2025/11/30',
        ]);
        $correction_id = $attendance_correction->id;

        $responseDetail = $this->get("/admin/stamp_correction_request/approve/{$correction_id}");
        $this->assertAuthenticatedAs($admin, guard: 'admin');

        $responseDetail->assertSeeInOrder([
            '名前',
            '山田',
            '太郎',
        ]);
        $responseDetail->assertSeeInOrder([
            '日付',
            '2025年',
            '11月1日',
        ]);
        $responseDetail->assertSeeInOrder([
            '出勤・退勤',
            '09:00',
            '～',
            '18:00',
        ]);
        $responseDetail->assertSeeInOrder([
            '休憩',
            '12:00',
            '～',
            '13:00',
        ]);
        $responseDetail->assertSeeInOrder([
            '備考',
            '修正テスト',
        ]);
    }

    // 修正待ちに申請が表示される
    public function test_correction_pending_list()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'reason' => '修正テスト'
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // ① AttendanceCorrection に登録されたか
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト',
        ]);

        $attendance_correction = AttendanceCorrection::first();

        // ② BreakCorrection に登録されたか
        $this->assertDatabaseHas('break_corrections', [
            'attendance_correction_id' => $attendance_correction->id,
            'break_start_correction' => '12:00:00',
            'break_end_correction' => '13:00:00',
        ]);

        // ステータスは 302 で OK
        $responsePost->assertStatus(302);

        // 申請一覧をリクエスト
        $responseList = $this->get("/stamp_correction_request/list");

        $responseList->assertSeeInOrder([
            '承認待ち',
            '山田 太郎',
            '2025/11/01',
            '修正テスト',
            '2025/11/30',
        ]);
    }

    // 修正済みに申請が表示される
    public function test_correction_approved_list()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // 管理者登録
        $admin = Admin::factory()->create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('12345678'),
        ]);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'reason' => '修正テスト'
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // ① AttendanceCorrection に登録されたか
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト',
        ]);

        $attendance_correction = AttendanceCorrection::first();

        // ② BreakCorrection に登録されたか
        $this->assertDatabaseHas('break_corrections', [
            'attendance_correction_id' => $attendance_correction->id,
            'break_start_correction' => '12:00:00',
            'break_end_correction' => '13:00:00',
        ]);

        // ステータスは 302 で OK
        $responsePost->assertStatus(302);

        // 管理者ログイン
        $this->actingAs($admin, 'admin');

        $correction_id = $attendance_correction->id;

        // 申請詳細をリクエスト
        $responseDetail = $this->get("/admin/stamp_correction_request/approve/{$correction_id}");
        $this->assertAuthenticatedAs($admin, guard: 'admin');

        //承認実行
        $postApproveData = [
            'attendance_correct_request_id' => $attendance_correction->id,
            'clock_in' => $attendance_correction->clock_in_correction_formatted,
            'clock_out' => $attendance_correction->clock_out_correction_formatted,
            'breaks' => [
                ['break_start' => $break_time->break_start_correction_formatted,
                 'break_end' => $break_time->break_end_correction_formatted,
                ],
            ],
            'reason' => $attendance_correction->reason_correction,
        ];

        $responseApprovePost = $this->actingAs($admin)
            ->post("/admin/stamp_correction_request/approve/exec", $postApproveData);

        // ステータスは 302 で OK
        $responseApprovePost->assertStatus(302);

        // ユーザ認証で申請一覧の承認済みタブをリクエスト
        $responseList = $this->actingAs($user)->get("/stamp_correction_request/list?tab=approved");

        $responseList->assertSeeInOrder([
            '承認済み',
            '山田 太郎',
            '2025/11/01',
            '修正テスト',
            '2025/11/30',
        ]);
    }

    // 申請から勤怠詳細画面へ遷移する
    public function test_correction_list_detail()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'reason' => '修正テスト'
        ];

        $responsePost = $this->actingAs($user)
            ->post("/attendance/detail/correction", $postData);

        // ① AttendanceCorrection に登録されたか
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト',
        ]);

        $attendance_correction = AttendanceCorrection::first();

        // ② BreakCorrection に登録されたか
        $this->assertDatabaseHas('break_corrections', [
            'attendance_correction_id' => $attendance_correction->id,
            'break_start_correction' => '12:00:00',
            'break_end_correction' => '13:00:00',
        ]);

        // ステータスは 302 で OK
        $responsePost->assertStatus(302);

        // 申請一覧をリクエスト
        $responseList = $this->get("/stamp_correction_request/list");

        $responseList->assertSeeInOrder([
            '承認待ち',
            '山田 太郎',
            '2025/11/01',
            '修正テスト',
            '2025/11/30',
        ]);

        $id = $attendance -> id;
        
        // 「詳細」リンクの遷移先を取得
        $responseDetail = $this->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $responseDetail->assertStatus(200);
    }
}    