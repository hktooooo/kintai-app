@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css')}}">
@endsection

@section('content')
<div class="attendance__detail__content">
    <h1 class="attendance__detail__header">勤怠詳細</h1>
    @php
        // 最新の修正申請（なければ null）
        $latestCorrection = $attendance->corrections->last();
        // 承認待ちかどうか
        $isPending = $latestCorrection && $latestCorrection->approval_status === 'pending';
        [$last_name, $first_name] = explode(' ', $attendance->user->name);
        [$year, $month_day] = explode(' ', $attendance->work_date_japanese);
    @endphp

    <form action="{{ route('submit.detail.correction') }}" method="post">
    @csrf
        <input type="hidden" name="id" id="id" value="{{ $attendance->id }}">
        <table class="attendance__detail__table">
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">名前</th>
                <td class="attendance__detail__data detail__name">
                    {{ $last_name }}<span>{{ $first_name }}</span>
                </td>
            </tr>
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">日付</th>
                <td class="attendance__detail__data detail__date">
                    <span class="attendance__detail__data__year">{{ $year }}</span>{{ $month_day }}
                </td>
            </tr>
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">出勤・退勤</th>
                <td class="attendance__detail__data">
                    <div class="detail__time">
                        <input type="text" name="clock_in" id="clock_in" value="{{ $attendance->clock_in_formatted }}" @if($isPending) disabled @endif>
                        <p>～</p>
                        <input type="text" name="clock_out" id="clock_out" value="{{ $attendance->clock_out_formatted }}" @if($isPending) disabled @endif>
                    </div>
                </td>
            </tr>
            @foreach ($break_times as $break_time)
                <tr class="attendance__detail__row">
                    <th class="attendance__detail__label">
                        休憩
                        @if ($loop->iteration > 1)
                            {{ $loop->iteration }}
                        @endif
                    </th>
                    <td class="attendance__detail__data">
                        <input type="hidden" name="breaks[{{ $break_time->id }}][id]" value="{{ $break_time->id }}">
                        <div class="detail__time">
                            <input type="text" name="breaks[{{ $break_time->id }}][break_start]" value="{{ $break_time->break_start_formatted }}" @if($isPending) disabled @endif>
                            <p>～</p>
                            <input type="text" name="breaks[{{ $break_time->id }}][break_end]" value="{{ $break_time->break_end_formatted }}" @if($isPending) disabled @endif>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">備考</th>
                <td class="attendance__detail__data detail__reason">
                    <textarea name="reason" id="reason" @if($isPending) disabled @endif>{{ $attendance->reason }}</textarea>
                </td>
            </tr>
        </table>
        <div class="attendance__detail__status">
            @if($latestCorrection && $latestCorrection->approval_status === 'pending')
                {{-- 承認待ちの場合 --}}
                <p class="attendance__detail__status__pending">
                    *承認待ちのため修正はできません。
                </p>
            @else
                {{-- 承認待ちでない場合（初回 or 却下済） --}}
                <button class="attendance__detail__status__submit btn" type="submit">
                    修正
                </button>
            @endif
        </div>
    </form>
</div>
@endsection