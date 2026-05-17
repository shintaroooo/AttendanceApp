<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;

class AdminCorrectionTest extends TestCase
{
        use RefreshDatabase;


    public function test_承認待ち修正申請が全て表示される() ///
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-05-01',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '申請中',
            'note' => '電車遅延のため',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.correction.list'));

        $response->assertSee('山田太郎');
    }

    public function test_承認済み修正申請が全て表示される() ///
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '佐藤花子',
        ]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-01',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認済み',
            'note' => '修正済み',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.correction.list', [
                'status' => 'approved',
            ]));

        $response->assertSee('佐藤花子');
    }

    public function test_修正申請の詳細内容が正しく表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 'finished',
            'note' => '体調不良のため',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'note' => '体調不良のため',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.correction.detail', $correction->id));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_修正申請の承認が正しく行われる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-05-01',
            'status' => 'finished',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'note' => '修正申請',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.correction.approve', $correction->id));

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => '承認済み',
        ]);
    }
}
