@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css')}}">
@endsection

@section('content')
<div class="">
    <h1>勤怠詳細</h1>
    <table>
        <tr>
            <th>名前</th>
            <td>あああ</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>2023年 6月 1日</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>09:00～18:00</td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>12:00～13:00</td>
        </tr>
        <tr>
            <th>備考</th>
            <td>電車遅延のため</td>
        </tr>
    </table>
    <div>
        <button>修正</button>
        <p>*承認待ちのため修正はできません。</p>
    </div>
</div>
@endsection('content')