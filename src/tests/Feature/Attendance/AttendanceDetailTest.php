<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
class AttendanceDetailTest extends TestCase
{
        use RefreshDatabase;

    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));

        $response->assertSee('山田太郎');
    }

    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));

        $response->assertSee('2026年');
        $response->assertSee('5月13日');
    }

    public function test_出勤時間と退勤時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'clock_in_at' => '2026-05-13 09:00:00',
            'clock_out_at' => '2026-05-13 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_休憩に表示される時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => '2026-05-13 12:00:00',
            'end_at' => '2026-05-13 13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
