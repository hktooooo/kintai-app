<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    // 出勤情報なし ⇒勤務外の表示
    public function test_status_completed()
    {
        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // ログインしてリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password1',
        ]);

        $this->assertAuthenticatedAs($user);

        // 出勤情報なし
        $response->assertSee('勤務外');
    }

    // 出勤時間のみあり ⇒出勤中の表示
    public function test_status_working()
    {
        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

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

        // ログインしてリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password1',
        ]);

        $this->assertAuthenticatedAs($user);

        // 出勤時間あり
        $response->assertSee('出勤中');
    }

}