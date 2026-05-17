<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
        use RefreshDatabase;

        public function test_その日の全ユーザーの勤怠情報が正確に確認できる()
        {
                Carbon::setTestNow('2026-05-01');

                $admin = User::factory()->create([
                        'role' => 'admin'
                ]);

                $user1 = User::factory()->create([
                        'name' => '山田太郎',
                ]);
                $user2 = User::factory()->create([
                        'name' => '佐藤花子',
                ]);

                Attendance::create([
                        'user_id' => $user1->id,
                        'work_date' => '2026-05-01',
                        'clock_in_at' => '09:00',
                        'clock_out_at' => '18:00',
                        'status' => 'finished',
                ]);

                Attendance::create([
                        'user_id' => $user2->id,
                        'work_date' => '2026-05-01',
                        'clock_in_at' => '10:00',
                        'clock_out_at' => '19:00',
                        'status' => 'finished',
                ]);

                $response = $this->actingAs($admin)
                        ->get(route('admin.attendance.list'));
                        $response->assertSee('山田太郎');
                        $response->assertSee('佐藤花子');
                        $response->assertSee('09:00');
                        $response->assertSee('18:00');
        }

        public function test_現在の日付が表示される()
        {
                Carbon::setTestNow('2026-05-01');

                $admin = User::factory()->create([
                        'role' => 'admin'
                ]);

                $response = $this->actingAs($admin)
                        ->get(route('admin.attendance.list'));
                        $response->assertSee('2026/05/01');
        }

        public function test_前日ボタンを押すと前日の勤怠情報が表示される()
        {
                $admin = User::factory()->create([
                        'role' => 'admin'
                ]);

                $response = $this->actingAs($admin)
                        ->get(route('admin.attendance.list', [
                                'date' => '2026-04-30'
                        ]));

                $response->assertSee('2026/04/30');
        }

        public function test_翌日ボタンを押すと翌日の勤怠情報が表示される()
        {
                $admin = User::factory()->create([
                        'role' => 'admin'
                ]);

                $response = $this->actingAs($admin)
                        ->get(route('admin.attendance.list', [
                                'date' => '2026-05-02'
                        ]));

                $response->assertSee('2026/05/02');
        }
}
