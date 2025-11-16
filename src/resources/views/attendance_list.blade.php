@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css')}}">
@endsection

@section('content')
<div class="attendance__list__content">
    <h1 class="attendance__list__header">勤怠一覧</h1>

    <div class="attendance__list__month-select-box">
        <a class="attendance__list__prev-next-month" href="{{ url('/attendance/list?month=' . $prevMonth) }}">← 前月</a>
        <p class="attendance__list__this-month">{{ $current->format('Y/n') }}</p> 
        <a class="attendance__list__prev-next-month" href="{{ url('/attendance/list?month=' . $nextMonth) }}">翌月 →</a>
    </div>

    <table class="attendance__list__table">
        <tr class="attendance__list__table__row">
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
            <tr class="attendance__list__table__row">
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