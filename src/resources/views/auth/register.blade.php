@extends('layouts.app')

@section('title', '会員登録')

@section('content')

    <div class="form-container">

        <h2 class="form-title">会員登録</h2>

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

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- 名前 --}}
            <div class="form-group">
                <label>名前</label>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- メール --}}
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

            {{-- パスワード確認 --}}
            <div class="form-group">
                <label>パスワード確認</label>
                <input type="password" name="password_confirmation">
            </div>

            {{-- ボタン --}}
            <button type="submit" class="btn-primary">登録する</button>

        </form>

        {{-- ログインリンク --}}
        <div class="form-link">
            <a href="{{ route('login') }}">ログインはこちら</a>
        </div>

    </div>

@endsection
