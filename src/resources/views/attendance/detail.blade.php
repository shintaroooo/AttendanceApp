@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')

    <div class="container">

        <h2 class="page-title">勤怠詳細</h2>

        @php
            $isPending =
                $attendance && $attendance->latestCorrection && $attendance->latestCorrection->status === '申請中';
            $targetDate = $attendance ? $attendance->work_date : $date;
        @endphp

        {{-- ★ フォーム開始 --}}
        @if (!$isPending)
            <form method="POST" action="{{ route('attendance.correction.store') }}">
                @csrf
                <input type="hidden" name="work_date" value="{{ $targetDate }}">
                @if ($attendance)
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                @endif
        @endif

        <div class="detail-card">

            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value grid-2">{{ auth()->user()->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value grid-2">
                    <div>{{ \Carbon\Carbon::parse($targetDate)->format('Y年') }}</div>
                    <div></div>
                    <div>{{ \Carbon\Carbon::parse($targetDate)->format('n月j日') }}</div>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value grid-2">
                    @if (!$isPending)
                        <input type="time" name="clock_in_at" value="{{ $attendance?->clock_in_at?->format('H:i') }}"
                            placeholder="00:00">
                        <div>〜</div>
                        <input type="time" name="clock_out_at" value="{{ $attendance?->clock_out_at?->format('H:i') }}"
                            placeholder="00:00">
                    @else
                        @php
                            $clockIn = $attendance?->latestCorrection?->items->where('field', 'clock_in_at')->first()
                                ?->after_value;
                            $clockOut = $attendance?->latestCorrection?->items->where('field', 'clock_out_at')->first()
                                ?->after_value;
                        @endphp

                        <div>{{ $clockIn ?? 'ー' }}</div>
                        <div>〜</div>
                        <div>{{ $clockOut ?? 'ー' }}</div>
                    @endif
                </div>
            </div>
            {{-- 休憩 --}}
            @php
                $breakTimes = $attendance ? $attendance->breakTimes : collect();
                //最後に空行を追加
                $breakTimes->push(null);
            @endphp
            @foreach ($breakTimes as $index => $break)
                <div class="row">
                    <div class="label">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</div>
                    <div class="value grid-2">
                        @if (!$isPending)
                            <input type="time" name="break_start_{{ $index }}"
                                value="{{ $break && $break->start_at ? \Carbon\Carbon::parse($break->start_at)->format('H:i') : '' }}"
                                placeholder="00:00">
                            <div>〜</div>
                            <input type="time" name="break_end_{{ $index }}"
                                value="{{ $break && $break->end_at ? \Carbon\Carbon::parse($break->end_at)->format('H:i') : '' }}"
                                placeholder="00:00">
                        @else
                            @php
                                $breakStart = $attendance?->latestCorrection?->items->firstWhere(
                                    'field',
                                    'break_start_0',
                                )?->after_value;

                                $breakEnd = $attendance?->latestCorrection?->items->firstWhere('field', 'break_end_0')
                                    ?->after_value;
                            @endphp

                            <div>{{ $breakStart ?? 'ー' }}</div>
                            <div>〜</div>
                            <div>{{ $breakEnd ?? 'ー' }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
            {{-- 備考 --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="value">
                    @if (!$isPending)
                        <textarea name="reason">{{ old('reason') }}</textarea>
                    @else
                        {{ $attendance?->latestCorrection?->reason }}
                    @endif
                </div>
            </div>
        </div>
        @if (!$isPending)
            <div class="action">
                <button type="submit" class="btn">修正</button>
            </div>
            </form> {{-- ★ フォーム終了 --}}
        @else
            <p class="error">
                ※承認待ちのため修正できません
            </p>
        @endif
    </div>
@endsection
