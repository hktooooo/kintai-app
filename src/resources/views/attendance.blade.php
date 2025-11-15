@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css')}}">
@endsection

@section('content')
<div class="attendance__content">
    <div class="attendance__main__wrapper">
        <div class="attendance__status-box">
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

        <div class="attendance__clock-section">
            <p class="attendance__clock-section__date">{{ $now->format('Y年m月d日') }}({{ $weekday }})</p>
            <p class="attendance__clock-section__time">{{ $now->format('H:i') }}</p>
        </div>

        <div class="attendance__button-box">
            @if ($status == "absent")
                <form action="{{ route('attendance.clockIn') }}" method="post">
                    @csrf
                    <input class="attendance__button__clockIn btn" type="submit" value="出勤">
                </form>
            @elseif ($status == "working")
                <div class="attendance__button-box__working" >
                    <form action="{{ route('attendance.clockOut') }}" method="post">
                        @csrf
                        <input class="attendance__button__clockOut btn" type="submit" value="退勤">
                    </form>
                    <form action="{{ route('attendance.breakStart') }}" method="post">
                        @csrf
                        <input class="attendance__button__breakStart btn" type="submit" value="休憩入">
                    </form>
                </div>
            @elseif ($status == "break")
                <form action="{{ route('attendance.breakEnd') }}" method="post">
                    @csrf
                    <input class="attendance__button__breakEnd btn" type="submit" value="休憩戻">
                </form>
            @elseif ($status == "completed")
                <p class="attendance__button-box__text">お疲れ様でした。</p>
            @else
                <p>エラー</p>
            @endif
        </div>
    </div>
</div>
@endsection