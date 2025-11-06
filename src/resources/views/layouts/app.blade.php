<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <a class="header__heading" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </a>
            @if (Auth::check())
            <nav>
                <ul class="header-nav">
                    <li class="header-nav__item">
                        <a class="" href="/attendance">勤怠</a>
                    </li>
                    <li class="header-nav__item">
                        <a class="" href="/attendance/list">勤怠一覧</a>
                    </li>
                    <li class="header-nav__item">
                        <a class="" href="{{ route('stamp_list')}}">申請</a>
                    </li>
                    <li class="header-nav__item">
                        <form action="/logout" method="post">
                        @csrf
                            <button class="header-nav__link-logout">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
            @endif
        </header>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

</html>