@extends('layouts.app')

@section('title', 'メール認証')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection
@section('content')

    <div class="verify-wrapper">

        <p class="verify-message">
            登録していただいたメールアドレスに確認メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="form-group">
            <a href="http://localhost:8025/" target="_blank" class="verify-button">
                認証はこちらから
            </a>
        </div>

        @if (session('status') == 'verification-link-sent')
            <p class="success-message">
                認証メールを再送しました。
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-link">
                認証メールを再送する
            </button>
        </form>

    </div>

@endsection
