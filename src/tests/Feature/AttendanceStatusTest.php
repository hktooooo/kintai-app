<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 5. ステータス確認機能
class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    // 出勤情報なし ⇒勤務外の表示
    public function test_status_absent()
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

        // 出勤情報なし
        $response->assertSee('勤務外');
    }

    // 出勤状態 ⇒出勤中の表示
    public function test_status_working()
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

        // 出勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => $now->format('H:i:s'),
            'status' => 'working',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        $response->assertSee('出勤中');
    }

    // 休憩状態 ⇒休憩中の表示
    public function test_status_break()
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

        // 出勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => $now->format('H:i:s'),
            'status' => 'break',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        $response->assertSee('休憩中');
    }

    // 退勤状態 ⇒退勤済の表示
    public function test_status_completed()
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

        // 出勤情報を登録
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

        $response->assertSee('退勤済');
    }
}