@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-common.css') }}">
@endsection

@section('body-class', 'bg-gray')
@section('content')

    <div class="container">

        <h2 class="page-title">スタッフ一覧</h2>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <a href="{{ route('admin.staff.attendance', $user->id) }}" class="detail-link">
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
