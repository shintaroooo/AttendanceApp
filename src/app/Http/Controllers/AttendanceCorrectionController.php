<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionItem;
use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionController extends Controller
{
    public function store(AttendanceCorrectionRequest $request)
    {
        //勤怠があるか確認
        $attendance = Attendance::firstOrCreate(
                [
                'user_id' => auth()->id(),
                'work_date' => $request->work_date,
                ],
                [
                'status' => 'finished',
                ]
            );
        $attendance->load('breakTimes');

        $exists = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('status', '申請中')
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', '既に申請中の修正があります');
        }

        DB::transaction(function () use ($request, $attendance) {

        //①修正申請の作成
        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'status' => '申請中',
            'reason' => $request->reason,
        ]);

        //②修正内容の保存（出勤・退勤）
        $this->saveItem($correction, 'clock_in_at', $attendance->clock_in_at, $request->clock_in_at);
        $this->saveItem($correction, 'clock_out_at', $attendance->clock_out_at, $request->clock_out_at);

        //③修正内容の保存（休憩）
        for ($i = 0; $i < 5; $i++) {
            $exitingBreak = $attendance->breakTimes[$i] ?? null;
            $this->saveItem(
                $correction,
                "break_start_{$i}",
                $exitingBreak?->start_at,
                $request->input("break_start_{$i}")
            );


            $this->saveItem(
                $correction,
                "break_end_{$i}",
                $exitingBreak?->end_at,
                $request->input("break_end_{$i}")
            );
        }
        });
        
        return redirect()->back()->with('success');

    }

    /* 修正差分を保存する */
    private function saveItem($correction, $field, $before, $after)
    {
        if ($before != $after && !is_null($after)) {
            $correction->items()->create([
                'field' => $field,
                'before_value' => $before,
                'after_value' => $after,
            ]);
        }
    }

    /* 自分の申請一覧を表示する */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = AttendanceCorrection::with('attendance');

        if ($status === 'pending') {
            $query->where('status', '申請中');
        } else {
            $query->where('status', '承認済み');
        }

        $corrections = $query
        ->where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

        return view('correction.list', compact('corrections', 'status'));
    }
}
