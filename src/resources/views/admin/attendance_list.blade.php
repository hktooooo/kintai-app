@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css')}}">
@endsection

@section('content')
<div class="attendance__list__content__wrapper">
    @php
        $formatted = $current->format('Y年m月d日');
    @endphp
    <div class="attendance__list__content">
        <h1 class="attendance__list__header">{{ $formatted }}の勤怠</h1>

        <div class="attendance__list__month-select-box">
            <a class="attendance__list__prev-next-month" href="{{ url('/admin/attendance/list?day=' . $prevDay) }}"><img src="{{ asset('images/image_prev.png') }}" alt="arrow_prev"><span>前日</span></a>
            <div class="attendance__list__this-month-calendar">
                <img src="{{ asset('images/image_calendar.png') }}" alt="calendar">
                <p class="attendance__list__this-month">{{ $current->format('Y/n') }}</p>
            </div>
            <a class="attendance__list__prev-next-month" href="{{ url('/admin/attendance/list?day=' . $nextDay) }}"><span>翌日</span><img src="{{ asset('images/image_next.png') }}" alt="arrow_next"></a>
        </div>

        <table class="attendance__list__table">
            <thead>
                <tr class="attendance__list__row">
                    <th class="attendance__list__label">名前</th>
                    <th class="attendance__list__label">出勤</th>
                    <th class="attendance__list__label">退勤</th>
                    <th class="attendance__list__label">休憩</th>
                    <th class="attendance__list__label">合計</th>
                    <th class="attendance__list__label">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr class="attendance__list__row">
                        <td class="attendance__list__data">{{ $attendance->user->name ?? '不明' }}</td>
                        <td class="attendance__list__data">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                        <td class="attendance__list__data">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</td>
                        <td class="attendance__list__data">{{ \Carbon\Carbon::parse($attendance->total_break)->format('H:i') }}</td>
                        <td class="attendance__list__data">{{ \Carbon\Carbon::parse($attendance->working_hours)->format('H:i') }}</td>
                        <td class="attendance__list__data">
                            <a href="{{ route('admin.detail', ['id' => $attendance->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection