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

                // その日の勤怠データを探す
                $attendanceForDate = $attendances->firstWhere('work_date', $date->toDateString());
            @endphp
            <tr>
                <td>
                    {{ $formatted }}
                </td>
                <td>
                    {{ $attendanceForDate && $attendanceForDate->clock_in
                        ? \Carbon\Carbon::parse($attendanceForDate->clock_in)->format('H:i')
                        : '-' }}
                </td>
                <td>
                    {{ $attendanceForDate && $attendanceForDate->clock_out
                        ? \Carbon\Carbon::parse($attendanceForDate->clock_out)->format('H:i')
                        : '-' }}
                </td>
                <td>
                    {{ $attendanceForDate && $attendanceForDate->total_break ? \Carbon\Carbon::parse($attendanceForDate->total_break)->format('H:i') : '-' }}
                </td>
                <td>
                    {{ $attendanceForDate ? \Carbon\Carbon::parse($attendanceForDate->working_hours)->format('H:i') : '-' }}
                </td>
                <td>
                    @if ($attendanceForDate)
                        <a href="{{ route('attendance.detail', ['id' => $attendanceForDate->id]) }}">
                            詳細
                        </a>
                    @else
                        <button disabled>詳細</button>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection