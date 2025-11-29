<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CurrentTest extends TestCase
{
    use RefreshDatabase;

    //現在の時刻が表示されているか
    public function test_login_user()
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

        // 今日の日付が画面に表示されているか確認
        $today = Carbon::today();
        $weekdays = ['日','月','火','水','木','金','土'];
        $weekday = $weekdays[$today->dayOfWeek];
        $formattedDate = $today->format('Y年m月d日') . "($weekday)";
        $response->assertSee($formattedDate);

        // 時間が画面に表示されているか確認
        $time = Carbon::now()->format('H:i');
        $response->assertSee($time);
    }
}