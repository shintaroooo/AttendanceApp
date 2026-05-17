@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-common.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')
    <section class="attendance">
        <div class="text-center">
            {{-- 状態表示 --}}
            @if (!$attendance)
                <p>勤務外</p>
            @elseif ($attendance->status === 'working')
                <p>出勤中</p>
            @elseif ($attendance->status === 'break')
                <p>休憩中</p>
            @elseif ($attendance->status === 'finished')
                <p>退勤済</p>
            @endif

            {{-- 日付 --}}
            <h2>{{ now()->isoFormat('Y年M月D日（ddd）') }}</h2>

            {{-- 時刻 --}}
            <h1 id="clock"></h1>

            {{-- ボタン表示 --}}
            @if (!$attendance)
                <form method="POST" action="/attendance/start">
                    @csrf
                    <button type="submit">出勤</button>
                </form>
            @elseif ($attendance->status === 'working')
                <form method="POST" action="/attendance/end">
                    @csrf
                    <button type="submit">退勤</button>
                </form>

                <form method="POST" action="/break/start">
                    @csrf
                    <button type="submit">休憩入</button>
                </form>
            @elseif ($attendance->status === 'break')
                <form method="POST" action="/break/end">
                    @csrf
                    <button type="submit">休憩戻</button>
                </form>
            @elseif ($attendance->status === 'finished')
                <p>お疲れ様でした。</p>
            @endif
        </div>

        {{-- 時計 --}}
        <script>
            function updateClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('ja-JP', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('clock').textContent = time;
            }
            setInterval(updateClock, 1000);
            updateClock();
        </script>
    </section>
@endsection
