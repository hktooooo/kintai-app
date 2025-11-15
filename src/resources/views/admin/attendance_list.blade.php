@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_list.css')}}">
@endsection

@section('content')
<div class="">
    @php
        $formatted = $current->format('Y年m月d日');
    @endphp
    <h1>{{ $formatted }}の勤怠</h1>

    <h2>{{ $current->format('Y/m/d') }} の日付一覧</h2>

    <p>
        <a href="{{ url('/admin/attendance/list?day=' . $prevDay) }}">← 前日</a> |
        <a href="{{ url('/admin/attendance/list?day=' . $nextDay) }}">翌日 →</a>
    </p>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name ?? '不明' }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->total_break)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->working_hours)->format('H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.detail', ['id' => $attendance->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection