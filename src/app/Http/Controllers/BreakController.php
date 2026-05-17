<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakController extends Controller
{
    //休憩開始ロジック
    public function start()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        //すでに休憩中の場合は何もしない
        $existingBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('end_at')
            ->first();
        if ($existingBreak) {
            return back();
        }
        
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_at' => now(),
        ]);
        $attendance->update([
            'status' => 'break',
        ]);
        return redirect()->back();
    }

    //休憩終了ロジック
    public function end()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('end_at')
            ->latest()
            ->first();

        $break->update([
            'end_at' => now(),
        ]);
        $attendance->update([
            'status' => 'working',
        ]);

        return redirect()->back();
    }
}
