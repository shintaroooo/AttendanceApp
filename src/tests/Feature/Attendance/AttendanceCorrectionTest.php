<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;

class AttendanceCorrectionTest extends TestCase
{
        use RefreshDatabase;

    public function test_出勤時間が退勤時間より後の場合バリデーションエラーになる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->post(route('attendance.correction.store'), [
                'attendance_id' => $attendance->id,
                'work_date' => '2026-05-13',
                'clock_in_at' => '18:00',
                'clock_out_at' => '17:00',
                'reason' => '修正理由',
            ]);

        $response->assertSessionHasErrors('clock_out_at');
    }

    public function test_休憩開始時間が退勤時間より後の場合バリデーションエラーになる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->post(route('attendance.correction.store'), [
                'attendance_id' => $attendance->id,
                'work_date' => '2026-05-13',
                'clock_in_at' => '09:00',
                'clock_out_at' => '17:00',
                'break_start_0' => '18:00',
                'break_end_0' => '19:00',
                'reason' => '修正理由',
            ]);

        $response->assertSessionHasErrors('break_start_0');
    }

    public function test_休憩終了時間が退勤時間より後の場合バリデーションエラーになる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->post(route('attendance.correction.store'), [
                'attendance_id' => $attendance->id,
                'work_date' => '2026-05-13',
                'clock_in_at' => '09:00',
                'clock_out_at' => '17:00',
                'break_start_0' => '12:00',
                'break_end_0' => '18:00',
                'reason' => '修正理由',
            ]);

        $response->assertSessionHasErrors('break_end_0');
    }

    public function test_備考未入力の場合バリデーションエラーになる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->post(route('attendance.correction.store'), [
                'attendance_id' => $attendance->id,
                'work_date' => '2026-05-13',
                'clock_in_at' => '09:00',
                'clock_out_at' => '17:00',
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)
            ->post(route('attendance.correction.store'), [
                'attendance_id' => $attendance->id,
                'work_date' => '2026-05-13',
                'clock_in_at' => '09:00',
                'clock_out_at' => '17:00',
                'reason' => '修正理由',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '修正理由',
            'status' => '申請中',
        ]);
    }

    public function test_承認待ちに自分の申請が全て表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '申請中',
            'reason' => '修正理由',
        ]);

        $response = $this->actingAs($user)
            ->get(route('correction.list'));

        $response->assertSee('修正理由');
    }

    public function test_承認済みに管理者承認済み申請が全て表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認済み',
            'reason' => '承認済み申請',
        ]);

        $response = $this->actingAs($user)
            ->get(route('correction.list', [
                'status' => 'approved',
            ]));

        $response->assertSee('承認済み申請');
    }

    public function test_各申請の詳細ボタンから勤怠詳細画面に遷移できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-13',
            'status' => 'finished',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '申請中',
            'reason' => '修正理由',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
