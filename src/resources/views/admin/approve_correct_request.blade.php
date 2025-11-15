@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css')}}">
@endsection

@section('content')
<div class="">
    <h1>勤怠詳細</h1>

    @php
        // 最新の修正申請（なければ null）
        $latestCorrection = $attendance->corrections->last();
        // 承認待ちかどうか
        $isPending = $latestCorrection && $latestCorrection->approval_status === 'pending';
    @endphp

    <form action="{{ route('admin.approve_correct_request_exec') }}" method="post">
    @csrf
        <input type="hidden" name="attendance_correct_request_id" id="attendance_correct_request_id" value="{{ $attendance_correct_request_id }}">
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
                <td>
                    <input type="text" name="clock_in" id="clock_in" value="{{ $attendance->clock_in }}" disabled>
                    ～
                    <input type="text" name="clock_out" id="clock_out" value="{{ $attendance->clock_out }}" disabled>
                </td>
            </tr>
            @foreach ($break_times as $break_time)
                <tr>
                    <th>
                        休憩
                        @if ($loop->iteration > 1)
                            {{ $loop->iteration }}
                        @endif
                    </th>
                    <td>
                        <input type="hidden" name="breaks[{{ $break_time->id }}][id]" value="{{ $break_time->id }}">
                        <input type="text" name="breaks[{{ $break_time->id }}][break_start]" value="{{ $break_time->break_start }}" disabled>
                        ～
                        <input type="text" name="breaks[{{ $break_time->id }}][break_end]" value="{{ $break_time->break_end }}" disabled>
                    </td>
                </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td>
                    <input type="text" name="reason" id="reason" value="{{ $attendance->reason }}" disabled>
                </td>
            </tr>
        </table>
        <div>
            @if($latestCorrection && $latestCorrection->approval_status === 'pending')
                {{-- 承認待ちの場合 --}}
                <button type="submit">承認</button>
            @else
                {{-- 承認済みの場合 --}}
                <p>承認済み</p>
            @endif
        </div>
    </form>
</div>
@endsection