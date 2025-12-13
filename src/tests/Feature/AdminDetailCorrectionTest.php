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

// ID 13. 勤怠詳細情報取得・修正機能（管理者）
class AdminDetailCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 管理者 勤怠詳細画面の表示が正しい
    public function test_admin_detail()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 20:00:00', 'Asia/Tokyo');
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
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'break_seconds' => 1 * 60 * 60,
        ]);

        $id = $attendance -> id;

        // 管理者の「詳細」リンクの遷移先を取得
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        // 詳細画面で表示される内容を確認
        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder([
            '名前',
            '山田',
            '太郎',
        ]);

        $response->assertSeeInOrder([
            '日付',
            '2025年',
            '11月1日',
        ]);

        $response->assertSeeInOrder([
            '出勤・退勤',
            '09:00',
            '～',
            '18:00',
        ]);
        $response->assertSeeInOrder([
            '休憩',
            '12:00',
            '～',
            '13:00',
        ]);
    }

    // 管理者 修正申請 出勤時間が退勤時間より後になっている
    public function test_admin_clock_in_validation()
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
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $id = $attendance -> id;

        // 管理者の「詳細」リンクの遷移先を取得
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ];

        // 修正データをポスト
        $responsePost = $this->actingAs($admin, 'admin')
            ->post("/admin/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // 管理者 修正申請 休憩開始時間が退勤時間より後になっている
    public function test_admin_break_start_validation()
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
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 管理者の「詳細」リンクの遷移先を取得
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '20:00', 'break_end' => '21:00'], // 不正値
            ]
        ];

        // 修正データをポスト
        $responsePost = $this->actingAs($admin, 'admin')
            ->post("/admin/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です',
        ]);
    }

    // 管理者 修正申請 休憩終了時間が退勤時間より後になっている
    public function test_admin_break_end_validation()
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
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 管理者の「詳細」リンクの遷移先を取得
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '21:00'], // 不正値
            ]
        ];

        // 修正データをポスト
        $responsePost = $this->actingAs($admin, 'admin')
            ->post("/admin/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }
    
    // 管理者 修正申請 備考欄が未入力
    public function test_admin_reason_validation()
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
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 休憩情報を登録
        $break_time = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $id = $attendance -> id;

        // 管理者の「詳細」リンクの遷移先を取得
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        $postData = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ]
        ];

        // 修正データをポスト
        $responsePost = $this->actingAs($admin, 'admin')
            ->post("/admin/attendance/detail/correction", $postData);

        // エラーメッセージが表示
        $responsePost->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }
}    