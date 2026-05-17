@extends('layouts.app')
@section('title', '勤怠一覧（管理者）')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-common.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')
    <div class="container">
        <h2 class="page-title">
            {{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠
        </h2>
        {{-- 日付操作 --}}
        <div class="date-nav">
            <a
                href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}">
                ← 前日 </a> <span class="current-date"> {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }} </span> <a
                href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}">
                翌日 → </a>
        </div> {{-- テーブル --}} <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php
                            $attendance = $user->attendances->where('work_date', $date)->first();
                            $breakMinutes = 0;
                            if ($attendance) {
                                foreach ($attendance->breakTimes as $break) {
                                    if ($break->start_at && $break->end_at) {
                                        $breakMinutes += \Carbon\Carbon::parse($break->start_at)->diffInMinutes(
                                            $break->end_at,
                                        );
                                    }
                                }
                                $workMinutes =
                                    $attendance->clock_in_at && $attendance->clock_out_at
                                        ? \Carbon\Carbon::parse($attendance->clock_in_at)->diffInMinutes(
                                            $attendance->clock_out_at,
                                        )
                                        : 0;
                                $totalMinutes = $workMinutes - $breakMinutes;
                            }
                        @endphp <tr>
                            <td>{{ $user->name }}</td>
                            <td> {{ optional($attendance?->clock_in_at)->format('H:i') }} </td>
                            <td> {{ optional($attendance?->clock_out_at)->format('H:i') }} </td>
                            <td> {{ $attendance ? gmdate('H:i', $breakMinutes * 60) : '' }} </td>
                            <td> {{ $attendance ? gmdate('H:i', $totalMinutes * 60) : '' }} </td>
                            <td>
                                <a href="{{ route('admin.attendance.detail.byDate', [
                                    'user_id' => $user->id,
                                    'date' => $date,
                                ]) }}"
                                    class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> @endsection
