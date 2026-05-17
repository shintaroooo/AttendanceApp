<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在日時情報がUIと同じ形式で出力される()
    {
        Carbon::setTestNow(
            Carbon::create(2026, 5, 11, 13, 30, 0) // テスト用の固定日時を設定
        );

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        //日付確認
        $response->assertSee(
            Carbon::now()->isoFormat('Y年M月D日（ddd）')
        );

    }
}
