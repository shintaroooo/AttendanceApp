<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;

class AttendanceStatusTest extends TestCase
{
        use RefreshDatabase;

    public function test_勤怠情報がない場合勤務外と表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合ステータスが表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('出勤中');
    }

    public function test_退勤済みの場合ステータスが表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now(),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('退勤済');
    }
}
