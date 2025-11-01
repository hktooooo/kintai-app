@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_list.css')}}">
@endsection

@section('content')
<div class="">
    @php
        $formatted = $now->format('Y年m月d日');
    @endphp
    <h1>{{ $formatted }}の勤怠</h1>

    <h2>{{ $now->format('Y/m/d') }} の日付一覧</h2>

    <p>
        <a href="{{ url('/attendance/list?month=' . $prevMonth) }}">← 前日</a> |
        <a href="{{ url('/attendance/list?month=' . $nextMonth) }}">翌日 →</a>
    </p>

    <table>
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        XXXX
            <tr>
                <td>
                    あああ
                </td>
                <td>
                    出勤時間
                </td>
                <td>
                    退勤時間
                </td>
                <td>
                    休憩時間
                </td>
                <td>
                    合計
                </td>
                <td>
                    <button>詳細</button>
                </td>
            </tr>
        XXX
    </table>
</div>
@endsection('content')