<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;



class AdminCorrectionController extends Controller
{
    //一覧
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $corrections = AttendanceCorrection::with('attendance.user')
            ->where('status', $status === 'approved' ? '承認済み' : '申請中')
            ->latest()
            ->get();

        return view('admin.correction.list', compact('corrections', 'status'));
    }

    //詳細
    public function show($id)
    {
        $correction = AttendanceCorrection::with([
            'attendance.user',
            'attendance.breakTimes',
            'items'
        ])->findOrFail($id);

        $attendance = $correction->attendance;

        //元データをベースに申請後の表示値を組み立てる
        $clockInAt = $attendance->clock_in_at;
        $clockOutAt = $attendance->clock_out_at;
        $reason = $correction->reason;

        $breaks =[];
        foreach ($attendance->breakTimes as $index => $break) {
            $breaks[$index] = [
                'start_at' => $break->start_at,
                'end_at' => $break->end_at,
            ];
        }

        foreach ($correction->items as $item) {
            if ($item->field === 'clock_in_at') {
                $clockInAt = $item->after_value;
            }
            if ($item->field === 'clock_out_at') {
                $clockOutAt = $item->after_value;
            }
            if (str_starts_with($item->field, 'break_start_')) {
                $index = (int) str_replace('break_start_', '', $item->field);

                if (!isset($breaks[$index])) {
                    $breaks[$index] = [
                        'start_at' => null,
                        'end_at' => null,
                    ];
                }
                $breaks[$index]['start_at'] = $item->after_value;
            }
            if (str_starts_with($item->field, 'break_end_')) {
                $index = (int) str_replace('break_end_', '', $item->field);

                if (!isset($breaks[$index])) {
                    $breaks[$index] = [
                        'start_at' => null,
                        'end_at' => null,
                    ];
                }
                $breaks[$index]['end_at'] = $item->after_value;
            }
        }
        ksort($breaks);
        return view('admin.correction.detail', compact(
            'correction',
            'clockInAt',
            'clockOutAt',
            'reason',
            'breaks'
        ));
    }

    //承認
    public function approve($id)
    {
        $correction = AttendanceCorrection::findOrFail($id);
        $attendance = Attendance::findOrFail($correction->attendance_id);

        //勤怠反映
        $attendance->update([
            'clock_in_at' => $correction->clock_in_at,
            'clock_out_at' => $correction->clock_out_at,
            'remark' => $correction->remark,
        ]);

        //ステータス変更
        $correction->update([
            'status' => '承認済み',
            ]);

        return redirect()->route('admin.correction.list');
    }
}
