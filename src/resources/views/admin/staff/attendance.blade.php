@extends('layouts.app')

@section('title', 'スタッフ月次勤怠')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-common.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')

    <div class="container">

        <h2 class="page-title">
            {{ $user->name }}さんの勤怠
        </h2>

        {{-- 月ナビ --}}
        <div class="date-nav">

            <a
                href="{{ route('admin.staff.attendance', [
                    'id' => $user->id,
                    'month' => $currentMonth->copy()->subMonth()->format('Y-m'),
                ]) }}">
                ← 前月
            </a>

            <div class="month-nav__current">
                {{ $currentMonth->format('Y/m') }}
            </div>

            <a
                href="{{ route('admin.staff.attendance', [
                    'id' => $user->id,
                    'month' => $currentMonth->copy()->addMonth()->format('Y-m'),
                ]) }}">
                翌月 →
            </a>

        </div>

        {{-- テーブル --}}
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $start = $currentMonth->copy()->startOfMonth();
                        $end = $currentMonth->copy()->endOfMonth();
                    @endphp

                    @for ($day = $start->copy(); $day <= $end; $day->addDay())
                        @php
                            $attendance = $attendances->where('work_date', $day->toDateString())->first();

                            $breakMinutes = 0;
                            $totalMinutes = 0;

                            if ($attendance) {
                                foreach ($attendance->breakTimes as $break) {
                                    if ($break->start_at && $break->end_at) {
                                        $breakMinutes += \Carbon\Carbon::parse($break->start_at)->diffInMinutes(
                                            $break->end_at,
                                        );
                                    }
                                }

                                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                                    $workMinutes = \Carbon\Carbon::parse($attendance->clock_in_at)->diffInMinutes(
                                        $attendance->clock_out_at,
                                    );

                                    $totalMinutes = $workMinutes - $breakMinutes;
                                }
                            }
                        @endphp

                        <tr>
                            {{-- 日付 --}}
                            <td>
                                {{ $day->format('m/d') }}
                                （{{ ['日', '月', '火', '水', '木', '金', '土'][$day->dayOfWeek] }}）
                            </td>

                            {{-- 出勤 --}}
                            <td>
                                {{ $attendance && $attendance->clock_in_at
                                    ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i')
                                    : '' }}
                            </td>

                            {{-- 退勤 --}}
                            <td>
                                {{ $attendance && $attendance->clock_out_at
                                    ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i')
                                    : '' }}
                            </td>

                            {{-- 休憩 --}}
                            <td>
                                {{ $attendance ? gmdate('H:i', $breakMinutes * 60) : '' }}
                            </td>

                            {{-- 合計 --}}
                            <td>
                                {{ $attendance ? gmdate('H:i', $totalMinutes * 60) : '' }}
                            </td>

                            {{-- 詳細 --}}
                            <td>
                                <a href="{{ route('admin.attendance.detail.byDate', [
                                    'user_id' => $user->id,
                                    'date' => $day->toDateString(),
                                ]) }}"
                                    class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- CSV --}}
        <div class="action">
            <a href="{{ route('admin.staff.attendance.csv', [
                'id' => $user->id,
                'month' => $currentMonth->format('Y-m'),
            ]) }}"
                class="btn">
                CSV出力
            </a>
        </div>

    </div>
@endsection
