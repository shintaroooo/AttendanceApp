<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = request('date')
        ? Carbon::parse(request('date'))->toDateString()
        : now()->toDateString();

        $users = User::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('work_date', $date);
        }, 'attendances.breakTimes'])
            ->get();

        return view('admin.attendance.list', compact('users', 'date'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])
            ->findOrFail($id);
        $user = $attendance->user;
        $date = $attendance->work_date;

        return view('admin.attendance.detail', compact('attendance', 'user', 'date'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // 日付の取得
        $date = Carbon::parse($attendance->work_date)->format('Y-m-d');
        //出勤・退勤をdatetimeに変換
        $clockIn = $request->clock_in_at
        ? Carbon::parse($date . ' ' . $request->clock_in_at)
        : null;
        $clockOut = $request->clock_out_at
        ? Carbon::parse($date . ' ' . $request->clock_out_at)
        : null;

        $attendance->update([
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'note' => $request->note,
        ]);
        // 休憩
        foreach($request->breaks ?? [] as $index => $break){
            if(isset($attendance->breakTimes[$index])){

            $start = $break['start']
            ? Carbon::parse($date . ' ' . $break['start'])
            : null;

            $end = $break['end']
            ? Carbon::parse($date . ' ' . $break['end'])
            : null;

            $attendance->breakTimes[$index]->update([
                'start_at' => $start,
                'end_at' => $end,
            ]);
        }
    }
        return back()->with('success', '修正しました');
    }

    public function staffList()
    {
        $users = User::where('role', 'user')->get();
        return view('admin.staff.list', compact('users'));
    }

    public function staffAttendance(Request $request, $id)
    {
        $currentMonth = Carbon::parse($request->input('month', now()));
        $user = User::findOrFail($id);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereBetween('work_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->get();

        return view('admin.staff.attendance', compact(
            'user',
            'attendances',
            'currentMonth'
        ));
    }
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : now();

        $attendance = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereBetween('work_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->orderBy('work_date')
            ->get();

        $response = new StreamedResponse(function () use ($attendance) {
            $handle = fopen('php://output', 'w');

            // ヘッダー行の出力
            $header = ['日付', '出勤', '退勤', '休憩', '合計'];
            $header = array_map(function ($value) {
                return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
            }, $header);
            fputcsv($handle, $header);

            foreach ($attendance as $attendance) {
                $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                    if ($break->start_at && $break->end_at) {
                        return Carbon::parse($break->start_at)
                            ->diffInMinutes($break->end_at);
                    }
                    return 0;
                });

                $workMinutes = $attendance->clock_in_at && $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_in_at)
                        ->diffInMinutes($attendance->clock_out_at)
                    : 0;

                $totalMinutes = $workMinutes - $breakMinutes;

                $row = [
                    Carbon::parse($attendance->work_date)->format('Y-m-d'),
                    optional($attendance->clock_in_at)->format('H:i'),
                    optional($attendance->clock_out_at)->format('H:i'),
                    gmdate('H:i', $breakMinutes * 60),
                    gmdate('H:i', $totalMinutes * 60),
                ];

                $row = array_map(function ($value) {
                    return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
                }, $row);
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        $fileName = 'attendance_' . $user->id . '_' . $currentMonth->format('Y_m') . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");

        return $response;
    }

    public function detailByDate(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $date = $request->date;

        $attendance = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->where('work_date', $date)
            ->first();

        return view('admin.attendance.detail', compact('attendance', 'user', 'date'));
    }

    public function updateOrCreate(AdminAttendanceUpdateRequest $request)
    {
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'work_date' => $request->work_date,
            ],
            [
                'clock_in_at' => $request->clock_in_at,
                'clock_out_at' => $request->clock_out_at,
                'note' => $request->note,
                'status' => 'finished',
            ]
        );
        // 休憩時間の更新・作成
        if ($request->breaks) {
            foreach ($request->breaks as $index => $break) {
                if(!empty($break['start']) && !empty($break['end'])){

                //既存休憩取得
                $breakTime = $attendance->breakTimes[$index] ?? null;
                if ($breakTime) {
                    // 既存の休憩時間を更新
                    $breakTime->update([
                        'start_at' => $request->work_date . ' ' . $break['start'],
                        'end_at' => $request->work_date . ' ' . $break['end'],
                    ]);
                } else {
                    // 新しい休憩時間を作成
                    $attendance->breakTimes()->create([
                        'start_at' => $request->work_date . ' ' . $break['start'],
                        'end_at' => $request->work_date . ' ' . $break['end'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.attendance.detail.byDate', [
                'user_id' => $request->user_id,
                'date' => $request->work_date,
            ])
            ->with('success', '修正しました');
    }
}
}
