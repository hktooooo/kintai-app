@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css')}}">
@endsection

@section('content')
<div class="">
    <h1>勤怠詳細</h1>

    <form action="{{ route('admin.detail.correction') }}" method="post">
    @csrf
        <input type="hidden" name="id" id="id" value="{{ $attendance->id }}">
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
                    <input type="text" name="clock_in" id="clock_in" value="{{ $attendance->clock_in }}">
                    ～
                    <input type="text" name="clock_out" id="clock_out" value="{{ $attendance->clock_out }}" >
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
                        <input type="text" name="breaks[{{ $break_time->id }}][break_start]" value="{{ $break_time->break_start }}">
                        ～
                        <input type="text" name="breaks[{{ $break_time->id }}][break_end]" value="{{ $break_time->break_end }}">
                    </td>
                </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td><input type="text" name="reason" id="reason" value="{{ $attendance->reason }}"></td>
            </tr>
        </table>
        <div>
            <button type="submit">修正</button>
        </div>
    </form>
</div>
@endsection