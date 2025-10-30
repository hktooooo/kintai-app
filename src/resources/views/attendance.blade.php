@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css')}}">
@endsection

@section('content')
<div class="register-form">
    <div>
        @if ($status == 0)
            勤務外
        @elseif ($status == 1)
            出勤中
        @elseif ($status == 2)
            休憩中
        @else
            退勤済
        @endif
    </div>

    <div>
        <p>今日は：{{ $now->format('Y年m月d日') }}（{{ $weekday }}）</p>
        <p>今の時間は：{{ $now->format('H:i:s') }}</p>
    </div>

    <div>
        <form class="register-form__form" action="/register" method="post">
            @csrf
            <input class="register-form__btn btn" type="submit" value="出勤">
        </form>
    </div>
</div>
@endsection('content')