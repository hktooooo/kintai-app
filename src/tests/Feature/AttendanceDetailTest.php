<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 10. 勤怠詳細情報取得機能（一般ユーザー）
class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細画面の「名前」
    public function test_detail_name()
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

        // 詳細画面で表示される内容を確認
        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder([
            '名前',
            '山田',
            '太郎',
        ]);
    }

    // 勤怠詳細画面の「日付」
    public function test_detail_date()
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

        // 詳細画面で表示される内容を確認
        $response->assertSee('勤怠詳細');

        $response->assertSeeInOrder([
            '日付',
            '2025年',
            '11月1日',
        ]);
    }

    // 勤怠詳細画面の「出勤・退勤」
    public function test_detail_clock_in_out()
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

        // 詳細画面で表示される内容を確認
        $response->assertSee('勤怠詳細');

        $response->assertSeeInOrder([
            '出勤・退勤',
            '09:00',
            '～',
            '18:00',
        ]);
    }

    // 勤怠詳細画面の「休憩」
    public function test_detail_break_start_end()
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

        // 詳細画面で表示される内容を確認
        $response->assertSee('勤怠詳細');

        $response->assertSeeInOrder([
            '休憩',
            '12:00',
            '～',
            '13:00',
        ]);
    }
}    