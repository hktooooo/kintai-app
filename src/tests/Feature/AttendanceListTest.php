<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 9. 勤怠一覧情報取得機能（一般ユーザー）
class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    // 自分の勤怠情報が全て表示されている
    public function test_all_attendances()
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

        // 2日分の出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);
    
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance/list');
        $this->assertAuthenticatedAs($user);
        
        $weekdays = ['日','月','火','水','木','金','土'];
        $date = Carbon::parse('2025-11-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';

        // 11/1の表示の確認
        $response->assertSeeInOrder([
            $formattedDate, // 11/01(土)
            '09:00',
            '18:00',
            '01:00',
            '08:00',
        ]);

        $date = Carbon::parse('2025-11-02');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';

        // 11/2の表示の確認
        $response->assertSeeInOrder([
            $formattedDate, // 11/02(日)
            '09:00',
            '18:00',
            '01:00',
            '08:00',
        ]);
    }

    // 勤怠一覧画面は現在の月を表示
    public function test_current_month()
    {
        // 固定日時を設定（テスト用 現在 11月）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance/list');
        $this->assertAuthenticatedAs($user);
        
        // 2025/11を確認
        $response->assertSee('2025/11');

        // 11/1と11/30を確認
        $response->assertSee('11/01');
        $response->assertSee('11/30');
    }

    // 勤怠一覧画面「前月」の表示
    public function test_prev_month()
    {
        // 固定日時を設定（テスト用 現在 11月）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 10月の出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance/list');
        $this->assertAuthenticatedAs($user);

        // 今月(11月)の表示確認
        $response->assertSee('2025/11');
        $response->assertSee('11/01');
        $response->assertSee('11/30');
        
        // 先月を作成
        $prevMonth = Carbon::now()->copy()->subMonth()->format('Y-m');

        // 「前月」リンクの遷移先を取得
        $responsePrev = $this->get("/attendance/list?month={$prevMonth}");
        
        // 先月(10月)の表示確認
        $responsePrev->assertSee('2025/10');
        $responsePrev->assertSee('10/01');
        $responsePrev->assertSee('10/31');

        $weekdays = ['日','月','火','水','木','金','土'];
        $date = Carbon::parse('2025-10-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';

        // 10/1の勤怠表示の確認
        $responsePrev->assertSeeInOrder([
            $formattedDate, // 10/01
            '09:00',
            '18:00',
            '01:00',
            '08:00',
        ]);
    }

    // 勤怠一覧画面「翌月」の表示
    public function test_next_month()
    {
        // 固定日時を設定（テスト用 現在 11月）
        $fixedNow = Carbon::parse('2025-11-30 18:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        // ユーザー登録・ログイン・メール認証
        $user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => now(),
        ]);

        // 10月の出退勤情報を登録
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-12-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_hours' => '08:00:00',
            'total_break' => '01:00:00',
            'status' => 'completed',
        ]);

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance/list');
        $this->assertAuthenticatedAs($user);

        // 今月(11月)の表示確認
        $response->assertSee('2025/11');
        $response->assertSee('11/01');
        $response->assertSee('11/30');
        
        // 先月を作成
        $nextMonth = Carbon::now()->copy()->addMonth()->format('Y-m');

        // 「前月」リンクの遷移先を取得
        $responseNext = $this->get("/attendance/list?month={$nextMonth}");
        
        // 先月(10月)の表示確認
        $responseNext->assertSee('2025/12');
        $responseNext->assertSee('12/01');
        $responseNext->assertSee('12/31');

        $weekdays = ['日','月','火','水','木','金','土'];
        $date = Carbon::parse('2025-12-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';

        // 12/1の勤怠表示の確認
        $responseNext->assertSeeInOrder([
            $formattedDate, // 12/01
            '09:00',
            '18:00',
            '01:00',
            '08:00',
        ]);
    }

    // 詳細画面へ遷移
    public function test_attendance_detail()
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

        // ログインして /attendance をリクエスト
        $response = $this->actingAs($user)->get('/attendance/list');
        $this->assertAuthenticatedAs($user);

        $id = $attendance -> id;
        
        // 「詳細」リンクの遷移先を取得
        $responseDetail = $this->get("/attendance/detail/{$id}");

        // ステータス 200（正常に開けた）を確認
        $responseDetail->assertStatus(200);

        // 詳細画面で表示される内容を確認（例：日付）
        $responseDetail->assertSee('勤怠詳細');
        $responseDetail->assertSee('11月1日');
        $responseDetail->assertSee('test_user');
        $responseDetail->assertSee('09:00');
        $responseDetail->assertSee('18:00');
    }
}