@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css')}}">
@endsection

@section('content')
<div class="">
    <h1>勤怠詳細</h1>
    <form action="{{ route('attendance.detail.correction', ['id' => $attendance->id]) }}" method="post">
    @csrf
        <table>
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $attendance->work_date }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td><input type="text" value="{{ $attendance->clock_in }}">～<input type="text" value="{{ $attendance->clock_out }}"></td>
            </tr>
            <tr>
                <th>休憩</th>
                <td><input type="text" value="">～<input value=""></td>
            </tr>
            <tr>
                <th>備考</th>
                <td><input type="text" value=""></td>
            </tr>
        </table>
        <div>
            <button>修正</button>
            <p>*承認待ちのため修正はできません。</p>
        </div>
    </form>
</div>
@endsection