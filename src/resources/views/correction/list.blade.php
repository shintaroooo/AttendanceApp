@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/correction.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')

    <div class="container">

        <h2 class="page-title">申請一覧</h2>

        {{-- タブ --}}
        <div class="tabs">
            <a href="{{ route('correction.list', ['status' => 'pending']) }}"
                class="{{ $status === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('correction.list', ['status' => 'approved']) }}"
                class="{{ $status === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
        </div>

        {{-- テーブル --}}
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($corrections as $correction)
                        <tr>
                            <td>{{ $correction->status }}</td>
                            <td>{{ $correction->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y/m/d') }}</td>
                            <td>{{ $correction->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('attendance.detail', $correction->attendance->id) }}"
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

@endsection
