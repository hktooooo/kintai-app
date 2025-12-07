@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/approve_correct_request.css')}}">
@endsection

@section('content')
<div class="approve__content">
    <h1 class="approve__header">勤怠詳細</h1>

    @php
        // 承認待ちかどうか
        $isPending = $attendance_correction->approval_status === 'pending';

        [$last_name, $first_name] = explode(' ', $attendance_correction->user->name);
        [$year, $month_day] = explode(' ', $attendance_correction->attendance->work_date_japanese);
    @endphp

    <form action="{{ route('admin.approve_correct_request_exec') }}" method="post">
    @csrf
        <input type="hidden" name="attendance_correct_request_id" id="attendance_correct_request_id" value="{{ $attendance_correct_request_id }}">
        <table class="approve__table">
            <tr class="approve__row">
                <th class="approve__label">名前</th>
                <td class="approve__data detail__name">
                    {{ $last_name }}<span>{{ $first_name }}</span>
                </td>
            </tr>
            <tr class="approve__row">
                <th class="approve__label">日付</th>
                <td class="approve__data detail__date">
                    <span class="approve__data__year">{{ $year }}</span>{{ $month_day }}
                </td>
            </tr>
            <tr class="approve__row">
                <th class="approve__label">出勤・退勤</th>
                <td>
                    <div class="detail__time">
                        <input type="text" name="clock_in" id="clock_in" value="{{ $attendance_correction->clock_in_correction_formatted }}" readonly>
                        <p>～</p>
                        <input type="text" name="clock_out" id="clock_out" value="{{ $attendance_correction->clock_out_correction_formatted }}" readonly>
                    </div>
                </td>
            </tr>
            @foreach ($break_time_corrections as $break_time)
                <tr class="approve__row">
                    <th class="approve__label">
                        休憩
                        @if ($loop->iteration > 1)
                            {{ $loop->iteration }}
                        @endif
                    </th>
                    <td class="approve__data">
                        <input type="hidden" name="breaks[{{ $break_time->id }}][id]" value="{{ $break_time->id }}" readonly>
                        <div class="detail__time">
                            <input type="text" name="breaks[{{ $break_time->id }}][break_start]" value="{{ $break_time->break_start_correction_formatted }}" readonly>
                            <p>～</p>
                            <input type="text" name="breaks[{{ $break_time->id }}][break_end]" value="{{ $break_time->break_end_correction_formatted }}" readonly>
                        </div>
                    </td>
                </tr>
            @endforeach
            {{-- ★ 新規追加用の1行 --}}
            <tr class="approve__row">
                <th class="approve__label">
                    休憩
                    @if ($break_time_corrections->count() >= 1)
                        {{ $break_time_corrections->count() + 1 }}
                    @endif
                </th>
                <td class="approve__data">
                </td>
            </tr>
            <tr class="approve__row">
                <th class="approve__label">備考</th>
                <td class="approve__data detail__reason">
                    <textarea name="reason" id="reason" readonly>{{ $attendance_correction->reason_correction }}</textarea>
                </td>
            </tr>
        </table>
        <div class="approve__exec__btn-box">
            @if($isPending)
                {{-- 承認待ちの場合 --}}
                <button class="approve__exec btn" type="submit">
                    承認
                </button>
            @else
                {{-- 承認済みの場合 --}}
                <p class="approve__executed btn">
                    承認済み
                </p>
            @endif
        </div>
    </form>
</div>
@endsection