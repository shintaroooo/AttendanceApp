<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;


class AdminLoginTest extends TestCase
{
        use RefreshDatabase;

    public function test_メールアドレス未入力の場合バリデーションエラー()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_パスワード未入力の場合バリデーションエラー()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_ログイン情報が一致しない場合ログイン失敗()
    {
            User::factory()->create([
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);

            $response = $this->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'wrongpassword',
            ]);

            $response->assertRedirect();
            $this->assertGuest();
    }

    public function test_正しいログイン情報の場合ログイン成功()
    {
            $admin = User::factory()->create([
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);

            $response = $this->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'password',
            ]);

            $this->assertAuthenticatedAs($admin);
            $response->assertRedirect();

    }
}