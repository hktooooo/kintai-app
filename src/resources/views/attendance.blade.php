@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance.css')}}">
@endsection

@section('content')
<div class="register-form">
    <div>
        @if ($status == "absent")
            勤務外
        @elseif ($status == "working")
            出勤中
        @elseif ($status == "break")
            休憩中
        @elseif ($status == "completed")
            退勤済
        @else
            エラー
        @endif
    </div>

    <div>
        <p>今日は：{{ $now->format('Y年m月d日') }}（{{ $weekday }}）</p>
        <p>今の時間は：{{ $now->format('H:i:s') }}</p>
    </div>

    <div>
        @if ($status == "absent")
            <form class="" action="{{ route('attendance.clockIn') }}" method="post">
                @csrf
                <input class="" type="submit" value="出勤">
            </form>
        @elseif ($status == "working")
            <form class="" action="{{ route('attendance.clockOut') }}" method="post">
                @csrf
                <input class="" type="submit" value="退勤">
            </form>
            <form class="" action="{{ route('attendance.breakStart') }}" method="post">
                @csrf
                <input class="" type="submit" value="休憩入">
            </form>
        @elseif ($status == "break")
            <form class="" action="{{ route('attendance.breakEnd') }}" method="post">
                @csrf
                <input class="" type="submit" value="休憩戻">
            </form>
        @elseif ($status == "completed")
            <p>お疲れ様でした。</p>
        @else
            <p>エラー</p>
        @endif
    </div>
</div>
@endsection