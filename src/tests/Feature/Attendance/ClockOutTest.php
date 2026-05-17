<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockOutTest extends TestCase
{
        use RefreshDatabase;

    public function test_退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(8),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/end');

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'finished',
        ]);

        $this->assertNotNull(
            Attendance::first()->clock_out_at);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => '2026-05-13 09:00:00',
            'clock_out_at' => '2026-05-13 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $response->assertSee('18:00');
    }
}
