@extends('layouts.app')

@section('title', 'ログイン')

@section('content')

    <div class="form-container">

        <h2 class="form-title">ログイン</h2>

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

        <form method="POST" action="{{ route('login') }}">

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
                ログインする
            </button>

        </form>

        {{-- 会員登録リンク --}}
        <div class="form-link">
            <a href="{{ route('register') }}">
                会員登録はこちら
            </a>
        </div>

    </div>

@endsection
