@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css')}}">
@endsection

@section('content')
<div class="">
    <h1>スタッフ一覧</h1>

    <table>
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
        </tr>
        @foreach ($users as $user)
            <tr>
                <td>
                    {{ $user -> name }}
                </td>
                <td>
                    {{ $user -> email }}
                </td>
                <td>
                    <a href="{{ route('admin.attendance_staff_list', ['id' => $user->id]) }}">
                        詳細
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection