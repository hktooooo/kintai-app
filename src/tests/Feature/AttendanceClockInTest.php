<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    // 出勤ボタンの機能確認
    public function test_clock_in_exec()
    {
        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        // 出勤ボタンの確認
        $response->assertSee('出勤');

        // 出勤をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/clock_in');

        // 状態「出勤中」を確認
        $response->assertSee('出勤中');
    }

    // 退勤済みユーザ ⇒出勤ボタンの非表示
    public function test_dont_see_attendance_button()
    {
        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 今日の日付と現在時刻
        $now = Carbon::now();
        $today = $now->toDateString();

        // 出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => $now->format('H:i:s'),
            'clock_out' => $now->format('H:i:s'),
            'status' => 'completed',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        $response->assertDontSee('出勤');
    }

    // 出勤時刻を勤怠一覧画面で確認できる
    public function test_clock_in_list()
    {
        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        // 出勤ボタンの確認
        $response->assertSee('出勤');

        // 現在時刻
        $now = Carbon::now();
        $timeStr = $now->format('H:i');

        // 出勤をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/clock_in');

        // 出勤リストを表示
        $response = $this->get('/attendance/list');

        // 出勤時間の表示を確認
        $response->assertSee($timeStr);
    }
}