<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakCorrection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 15. 勤怠情報修正機能（管理者）
class AdminStampCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 管理者 承認待ちの修正申請が全て表示される
    public function test_admin_correction_list_pending()
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

        // 出退勤情報を登録 3人分
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2025-11-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => '2025-11-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 修正申請登録 3人分
        $attendance_correction1 = AttendanceCorrection::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト1',
            'approval_status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        $attendance_correction2 = AttendanceCorrection::factory()->create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト2',
            'approval_status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        $attendance_correction3 = AttendanceCorrection::factory()->create([
            'user_id' => $user3->id,
            'attendance_id' => $attendance3->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト3',
            'approval_status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        // 管理者認証で申請一覧のをリクエスト
        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/list");

        $response->assertSeeInOrder([
            '承認待ち',
            'test_user1',
            '2025/11/01',
            '修正テスト1',
            '2025/11/30',
        ]);

        $response->assertSeeInOrder([
            '承認待ち',
            'test_user2',
            '2025/11/02',
            '修正テスト2',
            '2025/11/30',
        ]);

        $response->assertSeeInOrder([
            '承認待ち',
            'test_user3',
            '2025/11/03',
            '修正テスト3',
            '2025/11/30',
        ]);
    }

    // 管理者 承認済みの修正申請が全て表示される
    public function test_admin_correction_list_approved()
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

        // 出退勤情報を登録 3人分
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2025-11-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => '2025-11-03',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'working_seconds' => 8 * 60 * 60,
            'total_break_seconds' => 1 * 60 * 60,
            'status' => 'completed',
        ]);

        // 修正済み登録 3人分
        $attendance_correction1 = AttendanceCorrection::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト1',
            'approval_status' => 'approved',
            'requested_date' => now()->toDateString(),
        ]);

        $attendance_correction2 = AttendanceCorrection::factory()->create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト2',
            'approval_status' => 'approved',
            'requested_date' => now()->toDateString(),
        ]);

        $attendance_correction3 = AttendanceCorrection::factory()->create([
            'user_id' => $user3->id,
            'attendance_id' => $attendance3->id,
            'clock_in_correction' => '09:00:00',
            'clock_out_correction' => '18:00:00',
            'reason_correction' => '修正テスト3',
            'approval_status' => 'approved',
            'requested_date' => now()->toDateString(),
        ]);

        // 管理者認証で申請一覧のをリクエスト
        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/list?tab=approved");

        $response->assertSeeInOrder([
            '承認済み',
            'test_user1',
            '2025/11/01',
            '修正テスト1',
            '2025/11/30',
        ]);

        $response->assertSeeInOrder([
            '承認済み',
            'test_user2',
            '2025/11/02',
            '修正テスト2',
            '2025/11/30',
        ]);

        $response->assertSeeInOrder([
            '承認済み',
            'test_user3',
            '2025/11/03',
            '修正テスト3',
            '2025/11/30',
        ]);
    }

    // 管理者 修正申請の詳細が表示される
    public function test_admin_correction_detail()
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
            'name' => '山田 太郎',
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

        // 修正申請登録
        $attendance_correction1 = AttendanceCorrection::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'clock_in_correction' => '10:00:00',
            'clock_out_correction' => '17:00:00',
            'reason_correction' => '修正テスト1',
            'approval_status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        // 修正休憩申請登録
        $break_correction1 = BreakCorrection::factory()->create([
            'attendance_correction_id' => $attendance_correction1->id,
            'break_start_correction' => '11:00:00',
            'break_end_correction' => '12:00:00',
        ]);

        $id = $attendance_correction1->id;

        // 管理者認証で修正申請詳細をリクエスト
        $responseDetail = $this->actingAs($admin, 'admin')
            ->get("/admin/stamp_correction_request/approve/{$id}");

        $responseDetail->assertSeeInOrder([
            '名前',
            '山田',
            '太郎',
        ]);
        $responseDetail->assertSeeInOrder([
            '日付',
            '2025年',
            '11月1日',
        ]);
        $responseDetail->assertSeeInOrder([
            '出勤・退勤',
            '10:00',
            '～',
            '17:00',
        ]);
        $responseDetail->assertSeeInOrder([
            '休憩',
            '11:00',
            '～',
            '12:00',
        ]);
        $responseDetail->assertSeeInOrder([
            '備考',
            '修正テスト1',
        ]);
    }

    // 管理者 修正申請の承認処理が正しく行われる
    public function test_admin_correction_approve_exec()
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
            'name' => '山田 太郎',
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

        // 修正申請登録
        $attendance_correction1 = AttendanceCorrection::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'clock_in_correction' => '10:00:00',
            'clock_out_correction' => '17:30:00',
            'reason_correction' => '修正テスト1',
            'approval_status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        // 修正休憩申請登録
        $break_correction1 = BreakCorrection::factory()->create([
            'attendance_correction_id' => $attendance_correction1->id,
            'break_start_correction' => '11:00:00',
            'break_end_correction' => '11:30:00',
        ]);

        $id = $attendance_correction1->id;

        // 管理者認証で修正申請詳細をリクエスト
        $responseDetail = $this->actingAs($admin, 'admin')
            ->get("/admin/stamp_correction_request/approve/{$id}");


        $postData = [
            'attendance_correct_request_id' => $attendance_correction1->id,
            'clock_in' => $attendance_correction1->clock_in_correction,
            'clock_out' => $attendance_correction1->clock_out_correction,
            'breaks' => [
                [
                    'break_start' => $break_correction1->break_start_correction,
                    'break_end' => $break_correction1->break_end_correction
                ],
            ],
            'reason' => $attendance_correction1->reason_correction,
        ];

        $responsePost = $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/exec", $postData);

        // ステータスは 302 で OK
        $responsePost->assertStatus(302);

        // Attendanceに登録されたか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance1->id,
            'clock_in' => '10:00:00',
            'clock_out' => '17:30:00',
            'working_seconds' => 7 * 60 * 60,   // 8時間 ⇒7時間
            'total_break_seconds' => 0.5 * 60 * 60, // 1時間 ⇒30分
            'reason' => '修正テスト1',
        ]);

        // Breaksに登録されたか
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance1->id,
            'break_start' => '11:00:00',
            'break_end' => '11:30:00',
            'break_seconds' => 0.5 * 60 * 60, // 30分
        ]);
    }
}