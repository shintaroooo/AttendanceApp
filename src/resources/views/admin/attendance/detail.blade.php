@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">

@endsection

@section('body-class', 'bg-gray')
@section('content')

    <div class="container">

        <h2 class="page-title">勤怠詳細</h2>

        <form method="POST" action="{{ route('admin.attendance.save') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="work_date" value="{{ $date }}">

            <div class="detail-card">

                {{-- 名前 --}}
                <div class="row">
                    <div class="label">名前</div>
                    <div class="value grid-2">{{ $attendance?->user?->name ?? $user->name }}</div>
                </div>
                {{-- 日付 --}}
                <div class="row">
                    <div class="label">日付</div>
                    <div class="value grid-2">
                        @php
                            $targetDate = $attendance?->work_date ?? $date;
                        @endphp
                        <div>{{ \Carbon\Carbon::parse($targetDate)->format('Y年') }}</div>
                        <div></div>
                        <div>{{ \Carbon\Carbon::parse($targetDate)->format('n月j日') }}</div>
                    </div>

                </div>
                {{-- 出勤・退勤 --}}
                <div class="row">
                    <div class="label">出勤・退勤</div>
                    <div class="value grid-2">
                        <input type="time" name="clock_in_at" value="{{ $attendance?->clock_in_at?->format('H:i') }}"
                            placeholder="00:00">
                        <div>〜</div>
                        <input type="time" name="clock_out_at" value="{{ $attendance?->clock_out_at?->format('H:i') }}"
                            placeholder="00:00">
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
                            <input type="time" name="breaks[{{ $index }}][start]"
                                value="{{ $break && $break->start_at ? \Carbon\Carbon::parse($break->start_at)->format('H:i') : '' }}"
                                placeholder="00:00">
                            <div>〜</div>
                            <input type="time" name="breaks[{{ $index }}][end]"
                                value="{{ $break && $break->end_at ? \Carbon\Carbon::parse($break->end_at)->format('H:i') : '' }}"
                                placeholder="00:00">
                        </div>
                    </div>
                @endforeach

                {{-- 備考 --}}
                <div class="row">
                    <div class="label">備考</div>
                    <div class="value">
                        <textarea name="note">{{ $attendance?->note ?? '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="action">
                <button type="submit" class="btn">修正</button>
            </div>
        </form>
    </div>
@endsection
