@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/stamp_correction_request_list.css')}}">
@endsection

@section('content')
<div class="">
    <h1>申請一覧</h1>

    <div>
        <a class="stamp__list__link {{ $tab === 'pending' ? 'active' : '' }}" href="{{ route('stamp_list', ['tab' => 'pending']) }}">承認待ち</a>
        <a class="stamp__list__link {{ $tab === 'approved' ? 'active' : '' }}" href="{{ route('stamp_list', ['tab' => 'approved']) }}">承認済み</a>
    </div>

    <table>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>

        @foreach ($corrections as $correction)
            <tr>
                <td>
                    @if ($correction->approval_status === 'pending')
                        承認待ち
                    @elseif ($correction->approval_status === 'approved')
                        承認済み
                    @else
                        エラー
                    @endif
                </td>
                <td>
                    {{ $correction->user->name }}
                </td>
                <td>
                    {{ $correction->attendance->work_date }}
                </td>
                <td>
                    {{ $correction->attendance->reason }}
                </td>
                <td>
                    {{ $correction->requested_date }}
                </td>
                <td>
                    @if(Auth::guard('admin')->check())
                        <a href="{{ route('admin.approve_correct_request', ['attendance_correct_request_id' => $correction->attendance_id]) }}">
                            詳細
                        </a>
                    @elseif(Auth::check())
                        <a href="{{ route('attendance.detail', ['id' => $correction->attendance_id]) }}">
                            詳細
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection