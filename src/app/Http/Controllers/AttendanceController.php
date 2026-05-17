<?php

namespace App\Http\Controllers;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {

        //一覧表示（画面表示）
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();
        return view('attendance.index', compact('attendance'));
    }

    //出勤ロジック
    public function start()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->first();
        if ($attendance) {
            return redirect()->route('attendance.index');
        }
        Attendance::create([
            'user_id' => auth()->id(),
            'work_date' => today(),
            'clock_in_at' => now(),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index');
    }

    //退勤ロジック
    public function end()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->first();

        $attendance->update([
            'clock_out_at' => now(),
            'status' => 'finished',
        ]);

        return redirect()->route('attendance.index');
    }

    //勤怠一覧
    public function list(Request $request)
    {
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : now();

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date', 'asc')
            ->get();

        return view('attendance.list', compact('attendances', 'currentMonth'));
    }

    //勤怠詳細
    public function detail($id)
    {
        $attendance = Attendance::with('breakTimes', 'latestCorrection')
            ->findOrFail($id);

        return view('attendance.detail', compact('attendance'));
    }

    public function detailByDate($date)
    {
    $attendance = Attendance::with('breakTimes', 'latestCorrection')
        ->where('user_id', auth()->id())
        ->whereDate('work_date', $date)
        ->first();

    return view('attendance.detail', compact('attendance', 'date'));
    }

}