<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockInTest extends TestCase
{
        use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('出勤');

        $this->actingAs($user)->post('/attendance/start');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
        ]);
    }

    public function test_出勤は一日に一回のみ出勤できる()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertDontSee('<button type="submit">出勤</button>', false);
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => '09:00:00',
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSee('09:00');
    }

}
