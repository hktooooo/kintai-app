<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 12. 勤怠一覧情報取得機能（管理者）
class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    // その日の全ユーザーの勤怠情報が表示される
    public function test_admin_all_attendances()
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

        // ユーザー登録 3人分
        $user1 = User::factory()->create([
            'name' => 'test_user1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password1'),
        ]);

        $user2 = User::factory()->create([
            'name' => 'test_user2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password2'),
        ]);

        $user3 = User::factory()->create([
            'name' => 'test_user3',
            'email' => 'test3@example.com',
            'password' => bcrypt('password3'),
        ]);

        // 3人分の出退勤情報を登録
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-30',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);
    
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2025-11-30',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => '2025-11-30',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        // 日付表示の確認
        $response->assertSee('2025年11月30日の勤怠');
        
        // ユーザ1の勤怠確認
        $response->assertSeeInOrder([
            'test_user1', 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // ユーザ2の勤怠確認
        $response->assertSeeInOrder([
            'test_user2', 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // ユーザ3の勤怠確認
        $response->assertSeeInOrder([
            'test_user3', 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
    }

    // 現在の日付が表示される
    public function test_admin_attendances_today()
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

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        // 日付表示の確認
        $response->assertSee('2025年11月30日の勤怠');
        $response->assertSee('2025/11/30');
    }

    // 「前日」を押下した時に前の勤怠が表示される
    public function test_admin_attendances_prevday()
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

        // ユーザー登録
        $user1 = User::factory()->create([
            'name' => 'test_user1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password1'),
        ]);

        // 前日の出退勤情報を登録
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-29',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        // 現在の日付表示の確認
        $response->assertSee('2025年11月30日の勤怠');
        $response->assertSee('2025/11/30');

        // 前日を作成
        $prevDay = Carbon::now()->copy()->subDay()->toDateString();

        // 「前月」リンクの遷移先を取得
        $responsePrev = $this->get("/admin/attendance/list?day={$prevDay}");

        // 日付表示の確認
        $responsePrev->assertSee('2025年11月29日の勤怠');
        $responsePrev->assertSee('2025/11/29');

        // ユーザ1の勤怠確認
        $responsePrev->assertSeeInOrder([
            'test_user1', 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
    }

    // 「翌日」を押下した時に次の勤怠が表示される
    public function test_admin_attendances_nextday()
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

        // ユーザー登録
        $user1 = User::factory()->create([
            'name' => 'test_user1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password1'),
        ]);

        // 前日の出退勤情報を登録
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-12-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        // 現在の日付表示の確認
        $response->assertSee('2025年11月30日の勤怠');
        $response->assertSee('2025/11/30');

        // 前日を作成
        $nextDay = Carbon::now()->copy()->addDay()->toDateString();

        // 「前月」リンクの遷移先を取得
        $responseNext = $this->get("/admin/attendance/list?day={$nextDay}");

        // 日付表示の確認
        $responseNext->assertSee('2025年12月01日の勤怠');
        $responseNext->assertSee('2025/12/01');

        // ユーザ1の勤怠確認
        $responseNext->assertSeeInOrder([
            'test_user1', 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
    }
}