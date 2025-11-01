@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/stamp_correction_request_list.css')}}">
@endsection

@section('content')
<div class="">
    <h1>申請一覧</h1>

    <div>
        <a href="{{ route('stamp_list', ['tab' => '']) }}">承認待ち</a>
        <a href="{{ route('stamp_list', ['tab' => '']) }}">承認済み</a>
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

        {{-- @foreach ($dates as $date) --}}
            <tr>
                <td>
                    承認待ち
                </td>
                <td>
                    あああ
                </td>
                <td>
                    2023/06/01
                </td>
                <td>
                    遅延のため
                </td>
                <td>
                    2023/06/01
                </td>
                <td>
                    <button>詳細</button>
                </td>
            </tr>
        {{-- @endforeach --}}
    </table>
</div>
@endsection('content')