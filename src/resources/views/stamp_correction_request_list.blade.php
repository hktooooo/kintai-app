@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css')}}">
@endsection

@section('content')
<div class="stamp__list__content__wrapper">
    <div class="stamp__list__content">
        <h1 class="stamp__list__header">申請一覧</h1>

        <div class="stamp__list__link-box">
            <a class="stamp__list__link {{ $tab === 'pending' ? 'is-active' : '' }}"
            href="{{ route('stamp_list', ['tab' => 'pending']) }}">承認待ち</a>
            <a class="stamp__list__link {{ $tab === 'approved' ? 'is-active' : '' }}"
            href="{{ route('stamp_list', ['tab' => 'approved']) }}">承認済み</a>
        </div>

        <table class="stamp__list__table">
            <tr class="stamp__list__row">
                <th class="stamp__list__label">状態</th>
                <th class="stamp__list__label">名前</th>
                <th class="stamp__list__label">対象日時</th>
                <th class="stamp__list__label">申請理由</th>
                <th class="stamp__list__label">申請日時</th>
                <th class="stamp__list__label">詳細</th>
            </tr>

            @foreach ($corrections as $correction)
                <tr class="stamp__list__row">
                    <td class="stamp__list__data">
                        @if ($correction->approval_status === 'pending')
                            承認待ち
                        @elseif ($correction->approval_status === 'approved')
                            承認済み
                        @else
                            エラー
                        @endif
                    </td>
                    <td class="stamp__list__data">
                        {{ $correction->user->name }}
                    </td>
                    <td class="stamp__list__data">
                        {{ $correction->attendance->work_date_formatted }}
                    </td>
                    <td class="stamp__list__data ellipsis">
                        {{ $correction->reason_correction }}
                    </td>
                    <td class="stamp__list__data">
                        {{ $correction->requested_date_formatted }}
                    </td>
                    <td class="stamp__list__data">
                        @if(Auth::guard('admin')->check())
                            <a href="{{ route('admin.approve_correct_request', ['attendance_correct_request_id' => $correction->id]) }}">
                                詳細
                            </a>
                        @elseif(Auth::check())
                            @if($tab !== 'approved')
                                <a href="{{ route('attendance.detail', ['id' => $correction->attendance_id]) }}">
                                    詳細
                                </a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection