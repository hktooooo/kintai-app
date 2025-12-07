<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class MailVerifyTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録後、認証メールが送信される
    public function test_register_send_mail()
    {
        Notification::fake(); // MailではなくNotificationをフェイク

        // 1. ユーザー登録
        $this->post('/register', [
            'name' => 'validname',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 登録直後はログイン済み & 未認証
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());

        // Notificationが送信され、宛先が正しいか確認
        Notification::assertSentTo(
            $user,
            VerifyEmail::class,
            function ($notification, $channels) {
                // 'mail' チャネルで送信されたことを確認
                return in_array('mail', $channels);
            }
        );
    }

    // メール認証誘導画面で認証サイトに遷移する
    public function test_display_verify()
    {
        // 1. ユーザー登録
        $this->post('/register', [
            'name' => 'validname',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 登録直後はログイン済み & 未認証
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());

        // 「詳細」リンクの遷移先を取得
        $response = $this->actingAs($user)->get("/email/verify");

        // ステータス 200（正常に開けた）を確認
        $response->assertStatus(200);

        // 2. 認証リンクをシミュレート
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 3. 認証リンクアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // 認証後は redirect（302）
        $response->assertStatus(302);

        // 認証済みになったことを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    // メール認証を完了後、勤怠登録画面に遷移
    public function test_display_verify_enter_indexpage()
    {
        Event::fake(); // Verified イベントの発火を検証用にフェイクする

        // 1. ユーザー登録
        $this->post('/register', [
            'name' => 'validname',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 登録直後はログイン済み & 未認証
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());

        // 2. 認証リンクをシミュレート
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 3. 認証リンクアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // 画面が正常に表示されたか確認
        $response->assertStatus(302); // リダイレクトなら302
        $response->assertRedirect('/attendance'); // 期待する画面にリダイレクト

        // 認証済みになったことを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // Verifiedイベントが発火したことを確認
        Event::assertDispatched(Verified::class);
    }    
}
