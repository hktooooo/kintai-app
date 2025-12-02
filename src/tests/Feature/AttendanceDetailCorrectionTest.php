<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
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
}    