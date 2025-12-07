<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 8. 退勤機能
class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    // 退勤ボタンの機能確認
    public function test_clock_out_exec()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 今日の日付は Carbon::now() を使って固定される
        $today = Carbon::now()->toDateString(); // 2025-11-30

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 'working',
        ]);
    
        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        // 退勤ボタンの確認
        $response->assertSee('退勤');

        // 退勤をPostしリダイレクト先まで追従
        $response = $this->actingAs($user)
                        ->followingRedirects()
                        ->post('/attendance/clock_out');

        // POST 後に最新データを取得
        $attendance = Attendance::find($attendance->id);

        // DB 更新確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $today,
            'status' => 'completed',
        ]);

        // 状態「退勤済」を確認
        $response->assertSee('退勤済');

        // テスト終了後は Carbon の現在時刻を戻す
        Carbon::setTestNow();
    }

    // 退勤時刻を勤怠一覧画面で確認できる
    public function test_clock_in_list()
    {
        // 固定日時を設定（テスト用）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 今日の日付は Carbon::now() を使って固定される
        $today = Carbon::now()->toDateString(); // 2025-11-30

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 'working',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        // 現在時刻
        $timeStr = Carbon::now()->format('H:i'); // 18:00

        // 退勤をPostしリダイレクト先まで追従
        $response = $this->actingAs($user)
                        ->followingRedirects()
                        ->post('/attendance/clock_out');

        // DB 更新確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $today,
            'status' => 'completed',
        ]);

        // 出勤リストを表示
        $response = $this->get('/attendance/list');

        // 退勤時間の表示を確認
        $response->assertSee($timeStr); // 18:00

        // テスト終了後は Carbon の現在時刻を戻す
        Carbon::setTestNow();
    }
}