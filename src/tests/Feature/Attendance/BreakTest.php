<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTest extends TestCase
{
        use RefreshDatabase;

    public function test_休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)
            ->post('/break/start');

        $response->assertRedirect();
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => Attendance::first()->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'break',
        ]);
    }


    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        // 1回目の休憩開始
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => now()->subHours(),
            'end_at' => now()->subMinutes(30),
        ]);

        // 2回目の休憩開始
        $this->actingAs($user)->post('/break/start');
        $this->assertEquals(2, BreakTime::count());
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => now()->subMinutes(30),
        ]);

        $response = $this->actingAs($user)
            ->post('/break/end');

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'working',
        ]);

        $this->assertNotNull(
            BreakTime::first()->end_at);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => now()->subHour(),
            'end_at' => now()->subMinutes(30),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => now()->subMinutes(20),
        ]);

        $response = $this->actingAs($user)
            ->post('/break/end');

        $response->assertRedirect();

        $this->assertNotNull(
            BreakTime::latest()->first()->end_at
        );
    }

    public function test_休憩時間が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => '2026-01-01 12:00:00',
            'end_at' => '2026-01-01 13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $response->assertSee('01:00');
    }
}
