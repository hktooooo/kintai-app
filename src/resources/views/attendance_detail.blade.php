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

    {{ optional($attendance->corrections->last())->approval_status }}

    <form action="{{ route('submit.detail.correction') }}" method="post">
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
                <td><input type="text" name="clock_in" id="clock_in" value="{{ $attendance->clock_in }}" @if($isPending) disabled @endif>～<input type="text" name="clock_out" id="clock_out" value="{{ $attendance->clock_out }}" @if($isPending) disabled @endif></td>
            </tr>
            <tr>
                <th>休憩</th>
                <td><input type="text" value="">～<input value=""></td>
            </tr>
            <tr>
                <th>備考</th>
                <td><input type="text" name="reason" id="reason" value="{{ $attendance->reason }}" @if($isPending) disabled @endif></td>
            </tr>
        </table>
        <div>
            @if($latestCorrection && $latestCorrection->approval_status === 'pending')
                {{-- 承認待ちの場合 --}}
                <p>*承認待ちのため修正はできません。</p>
            @else
                {{-- 承認待ちでない場合（初回 or 却下済） --}}
                <button type="submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection