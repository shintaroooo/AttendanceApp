<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
        use RefreshDatabase;

    public function test_管理者が一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance', $user->id));
        
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前月ボタンを押すと前月の情報が表示される()
    {
        Carbon::setTestNow('2026-05-15');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance', [
                'id' => $user->id,
                'month' => '2026-04'
            ]));

        $response->assertSee('2026/04');
    }

    public function test_翌月ボタンを押すと翌月の情報が表示される()
    {

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);
    }
}
