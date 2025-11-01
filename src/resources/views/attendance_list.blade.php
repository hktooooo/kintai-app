@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_list.css')}}">
@endsection

@section('content')
<div class="">
    <h1>勤怠一覧</h1>

    <h2>{{ $current->format('Y年n月') }} の日付一覧</h2>

    <p>
        <a href="{{ url('/attendance/list?month=' . $prevMonth) }}">← 前月</a> |
        <a href="{{ url('/attendance/list?month=' . $nextMonth) }}">翌月 →</a>
    </p>

    <table>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>

        @foreach ($dates as $date)
            @php
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                $formatted = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
            @endphp
            <tr>
                <td>
                    {{ $formatted }}
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
        @endforeach
    </table>
</div>
@endsection('content')