<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>

<body class="@yield('body-class')">

    {{-- ヘッダー --}}
    <header class="header">
        <div class="header__logo">
            <a
                href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.attendance.list') : route('attendance.index') }}">
                <img class="header__logo-img" src="{{ asset('images/header_logo.png') }}" alt="COACHTECH ロゴ">
            </a>
        </div>

        <nav class="header__nav">
            {{-- 未ログイン --}}
            @guest
                {{-- ロゴのみ表示 --}}
            @endguest

            {{-- 一般ユーザー --}}
            @auth
                @if (auth()->user()->role === 'user')
                    <ul class="header__nav-menu">
                        <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                        <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('correction.list') }}">申請</a></li>
                        <li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                ログアウト
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                @endif
            @endauth

            {{-- 管理者 --}}
            @auth
                @if (auth()->user()->role === 'admin')
                    <ul class="header__nav-menu">
                        <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                        <li><a href="{{ route('admin.correction.list') }}">申請一覧</a></li>
                        <li>
                            <a href="{{ route('admin.logout') }}"
                                onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                ログアウト
                            </a>
                            <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                @endif
            @endauth

        </nav>
    </header>

    {{-- メイン --}}
    <main class="content">

        {{-- フラッシュメッセージ --}}
        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')

    </main>

</body>

</html>
