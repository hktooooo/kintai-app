@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css')}}">
@endsection

@section('content')
<div class="attendance__list__content">
    <h1 class="attendance__list__header">勤怠一覧</h1>

    <div class="attendance__list__month-select-box">
        <a class="attendance__list__prev-next-month" href="{{ url('/attendance/list?month=' . $prevMonth) }}"><img src="{{ asset('images/image_prev.png') }}" alt="arrow_prev"><span>前月</span></a>
        <div class="attendance__list__this-month-calendar">
            <img src="{{ asset('images/image_calendar.png') }}" alt="calendar">
            <p class="attendance__list__this-month">{{ $current->format('Y/n') }}</p>
        </div>
        <a class="attendance__list__prev-next-month" href="{{ url('/attendance/list?month=' . $nextMonth) }}"><span>翌月</span><img src="{{ asset('images/image_next.png') }}" alt="arrow_next"></a>
    </div>

    <table class="attendance__list__table">
        <tr class="attendance__list__row">
            <th class="attendance__list__label">日付</th>
            <th class="attendance__list__label">出勤</th>
            <th class="attendance__list__label">退勤</th>
            <th class="attendance__list__label">休憩</th>
            <th class="attendance__list__label">合計</th>
            <th class="attendance__list__label">詳細</th>
        </tr>

        @foreach ($dates as $date)
            @php
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                $formatted = $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';

                // その日の勤怠データを探す
                $attendanceForDate = $attendancesByDate[$date->toDateString()] ?? null;
            @endphp

            <tr class="attendance__list__row">
                <td class="attendance__list__data">
                    {{ $formatted }}
                </td>
                <td class="attendance__list__data">
                    {{ $attendanceForDate?->clock_in_formatted }}
                </td>
                <td class="attendance__list__data">
                    {{ $attendanceForDate?->clock_out_formatted }}
                </td class="attendance__list__data">
                <td class="attendance__list__data">
                    {{ $attendanceForDate?->total_break_hi }}
                </td>
                <td class="attendance__list__data">
                    {{ $attendanceForDate?->working_hours_hi }}
                </td>
                <td class="attendance__list__data">
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