<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-01',
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->dump();
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

}

    public function test_出勤時間が退勤時間より後の場合バリデーションエラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.save', [
                'user_id' => $user->id,
                'work_date' => '2026-05-01',
                'clock_in_at' => '18:00',
                'clock_out_at' => '16:00',
                'note' => '修正',
            ]));

        $response->assertSessionHasErrors(['clock_in_at']);
    }

    public function test_休憩開始時間が退勤時間より後の場合バリデーションエラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.save', [
                'user_id' => $user->id,
                'work_date' => '2026-05-01',
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'breaks' => [
                    [
                        'start' => '18:30',
                        'end' => '19:00',
                    ],
                ],
                'note' => '修正',
            ]));

        $response->assertSessionHasErrors(['breaks.0.start']);
}

    public function test_休憩終了時間が退勤時間より後の場合バリデーションエラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.save', [
                'user_id' => $user->id,
                'work_date' => '2026-05-01',
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'breaks' => [
                    [
                        'start' => '12:00',
                        'end' => '18:30',
                    ],
                ],
                'note' => '修正',
             ]));

        $response->assertSessionHasErrors(['breaks.0.end']);
    }

    public function test_備考未入力の場合バリデーションエラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.save', [
                'user_id' => $user->id,
                'work_date' => '2026-05-01',
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'note' => '',
             ]));

        $response->assertSessionHasErrors(['note']);
    }
}
