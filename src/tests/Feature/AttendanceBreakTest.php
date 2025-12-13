<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 7. 休憩機能
class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    // 休憩入ボタンの機能確認
    public function test_break_start_exec()
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

        // 休憩入ボタンの確認
        $response->assertSee('休憩入');

        // 休憩入をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_start');

        $response->assertSee('休憩中');
    }

    // 休憩は一日に何回でもできる
    public function test_break_start_repeat()
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

        // 休憩入ボタンの確認
        $response->assertSee('休憩入');

        // 休憩入をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_start');

        // 休憩戻をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_end');

        // 休憩入ボタンの再確認
        $response->assertSee('休憩入');
    }

    // 休憩戻ボタンの機能確認
    public function test_break_end_exec()
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

        // 休憩入ボタンの確認
        $response->assertSee('休憩入');

        // 休憩入をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_start');

        // 休憩戻をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_end');

        // 出勤中の確認
        $response->assertSee('出勤中');
    }

    // 休憩戻は一日に何回でもできる
    public function test_break_end_repeat()
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

        // 休憩入ボタンの確認
        $response->assertSee('休憩入');

        // 休憩入をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_start');

        // 休憩戻をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_end');

        // 休憩入を再Postしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_start');

        // 休憩戻の確認
        $response->assertSee('休憩戻');
    }

   // 休憩時間が勤怠一覧で確認できる
    public function test_break_list()
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
            'clock_in' => '01:00:00',
            'status' => 'working',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertAuthenticatedAs($user);

        // 休憩入をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_start');

        // 1分停止
        sleep(60); 

        // 休憩戻をPostしリダイレクト先まで追従
        $response = $this->followingRedirects()->post('/attendance/break_end');
        
        // 出勤リストを表示
        $response = $this->get('/attendance/list');

        // 休憩時間 1分の表示を確認
        $response->assertSee('0:01');
    }
}