<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //ログイン--メアドバリデーション
    public function test_login_admin_validate_email()
    {
        $response = $this->post('/admin/login', [
            'email' => "",
            'password' => "12345678",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ログイン--パスワードバリデーション
    public function test_login_admin_validate_password()
    {
        $response = $this->post('/admin/login', [
            'email' => "admin@test.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン--不一致
    public function test_login_admin_validate_user()
    {
        $response = $this->post('/admin/login', [
            'email' => "admin@test.com",
            'password' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }

    //ログイン機能
    public function test_login_admin()
    {
        $user = Admin::find(1);

        $response = $this->post('/admin/login', [
            'email' => "admin@test.com",
            'password' => "12345678",
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($user, 'admin');
    }
}