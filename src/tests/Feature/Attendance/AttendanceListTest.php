<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
        use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'clock_in_at' => '2026-05-13 09:00:00',
            'clock_out_at' => '2026-05-13 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee('05/13');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow('2026-05-15');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $response->assertSee('2026/05');
    }

    public function test_前月ボタンを押すと前月の情報が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('attendance.list', [
                'month' => '2026-04'
                ]));

        $response->assertSee('2026/04');
    }

    public function test_翌月ボタンを押すと翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('attendance.list', [
                'month' => '2026-06'
                ]));

        $response->assertSee('2026/06');
    }

    public function test_詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
    }
}