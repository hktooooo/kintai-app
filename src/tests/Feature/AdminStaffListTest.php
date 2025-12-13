<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 14. ユーザー情報取得機能（管理者）
class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    // 管理者が全一般ユーザーの氏名・メールアドレスを確認できる
    public function test_admin_all_staffs()
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

        // 管理者ログインして /admin/staff/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        // タイトル表示の確認
        $response->assertSee('スタッフ一覧');
        
        // ユーザ1の情報
        $response->assertSeeInOrder([
            'test_user1', 
            'test1@example.com',
        ]);

        // ユーザ2の情報
        $response->assertSeeInOrder([
            'test_user2', 
            'test2@example.com',
        ]);

        // ユーザ3の情報
        $response->assertSeeInOrder([
            'test_user3', 
            'test3@example.com',
        ]);
    }

    // ユーザーの今月の勤怠情報を表示
    public function test_admin_attendance_staff_list()
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

        // 出退勤情報を登録 今月 3日分
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        $id = $user1->id;

        // ユーザ勤怠一覧をリクエスト
        $responseList = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$id}");

        $weekdays = ['日','月','火','水','木','金','土'];

        // 11/1の勤怠
        $date = Carbon::parse('2025-11-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseList->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // 11/2の勤怠
        $date = Carbon::parse('2025-11-02');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseList->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // 11/3の勤怠
        $date = Carbon::parse('2025-11-03');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseList->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
    }

    // ユーザーの前月の勤怠情報を表示
    public function test_admin_attendance_staff_prev_list()
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

        // 出退勤情報を登録 先月 3日分
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-10-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-10-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-10-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        $id = $user1->id;

        // 先月を作成
        $prevMonth = Carbon::now()->copy()->subMonth()->format('Y-m');

        // ユーザ勤怠一覧をリクエスト
        $responsePrev = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$id}?month={$prevMonth}");

        $weekdays = ['日','月','火','水','木','金','土'];

        // 10/1の勤怠
        $date = Carbon::parse('2025-10-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responsePrev->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // 10/2の勤怠
        $date = Carbon::parse('2025-10-02');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responsePrev->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // 10/3の勤怠
        $date = Carbon::parse('2025-10-03');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responsePrev->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
    }

    // ユーザーの翌月の勤怠情報を表示
    public function test_admin_attendance_staff_next_list()
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

        // 出退勤情報を登録 翌月 3日分
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-12-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-12-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-12-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 管理者ログインして /admin/attendance/list をリクエスト
        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');
        $this->assertAuthenticatedAs($admin, guard: 'admin');
        
        $id = $user1->id;

        // 翌月を作成
        $nextMonth = Carbon::now()->copy()->addMonth()->format('Y-m');

        // ユーザ勤怠一覧をリクエスト
        $responseNext = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$id}?month={$nextMonth}");

        $weekdays = ['日','月','火','水','木','金','土'];

        // 12/1の勤怠
        $date = Carbon::parse('2025-12-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseNext->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // 12/2の勤怠
        $date = Carbon::parse('2025-12-02');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseNext->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        // 12/3の勤怠
        $date = Carbon::parse('2025-12-03');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseNext->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
    }

    // 今月の勤怠情報から勤怠詳細へ遷移
    public function test_admin_attendance_staff_detail()
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

        // 出退勤情報を登録
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);
        
        $id = $user1->id;

        // 管理者ログイン
        $this->actingAs($admin, 'admin');

        // ユーザ勤怠一覧をリクエスト
        $responseList = $this->get("/admin/attendance/staff/{$id}");
        $this->assertAuthenticatedAs($admin, guard: 'admin');

        $weekdays = ['日','月','火','水','木','金','土'];

        // 11/1の勤怠
        $date = Carbon::parse('2025-11-01');
        $formattedDate = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
        $responseList->assertSeeInOrder([
            $formattedDate, 
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);

        $attendance_id = $attendance1 -> id;

        // 「詳細」リンクの遷移先を取得
        $responseDetail = $this->get("/admin/attendance/detail/{$attendance_id}");

        // ステータス 200（正常に開けた）を確認
        $responseDetail->assertStatus(200);
    }
}