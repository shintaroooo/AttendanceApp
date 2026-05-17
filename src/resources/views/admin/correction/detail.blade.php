@extends('layouts.app')

@section('title', '修正申請承認画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')

    <div class="container">

        <h2 class="detail__title">勤怠詳細</h2>

        <div class="detail-card">

            <div class="row">
                <label>名前</label>
                <div>{{ $correction->attendance->user->name }}</div>
            </div>

            <div class="row">
                <label>日付</label>
                <div>
                    {{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y年') }}
                    {{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('n月j日') }}
                </div>
            </div>

            <div class="row">
                <label>出勤・退勤</label>
                <div>
                    {{ $clockInAt ? \Carbon\Carbon::parse($clockInAt)->format('H:i') : '' }}
                    〜
                    {{ $clockOutAt ? \Carbon\Carbon::parse($clockOutAt)->format('H:i') : '' }}
                </div>
            </div>

            @foreach ($breaks as $index => $break)
                <div class="row">
                    <label>{{ $index === 0 ? '休憩' : '休憩 ' . ($index + 1) }}</label>
                    <div>
                        {{ !empty($break['start_at']) ? \Carbon\Carbon::parse($break['start_at'])->format('H:i') : '' }}
                        〜
                        {{ !empty($break['end_at']) ? \Carbon\Carbon::parse($break['end_at'])->format('H:i') : '' }}
                    </div>
                </div>
            @endforeach

            <div class="row">
                <label>備考</label>
                <div>{{ $reason }}</div>
            </div>

        </div>

        {{-- 承認ボタン --}}
        @if ($correction->status === '申請中')
            <form method="POST" action="{{ route('admin.correction.approve', $correction->id) }}">
                @csrf
                <div class="action">
                    <button type="submit" class="btn">承認</button>
                </div>
            </form>
        @else
            <div class="action">
                <button type="button" class="btn disabled" disabled>承認済み</button>
            </div>
        @endif

    </div>

@endsection
