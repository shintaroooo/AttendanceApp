@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')

    <div class="form-container">

        <h2 class="form-title">管理者ログイン</h2>

        {{-- 全体エラー --}}
        @if ($errors->any())
            <div class="error-box">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf

            {{-- メールアドレス --}}
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- ログインボタン --}}
            <button type="submit" class="btn-primary">
                管理者ログインする
            </button>

        </form>

    </div>

@endsection
