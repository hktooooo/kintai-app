<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

// ID 4. 日時取得機能
class CurrentTest extends TestCase
{
    use RefreshDatabase;

    //現在の時刻が表示されているか
    public function test_login_user()
    {
        Carbon::setTestNow('2025-11-30 09:00:00');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ログイン状態を作る
        $response = $this->actingAs($user)->get('/attendance');

        $this->assertAuthenticatedAs($user);

        // 今日の日付が画面に表示されているか確認
        $today = Carbon::today();
        $weekdays = ['日','月','火','水','木','金','土'];
        $weekday = $weekdays[$today->dayOfWeek];
        $formattedDate = $today->format('Y年m月d日') . "($weekday)";
        $response->assertSee($formattedDate);

        // 時間が画面に表示されているか確認
        $response->assertSee('09:00');
    }
}