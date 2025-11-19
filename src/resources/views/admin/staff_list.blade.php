@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css')}}">
@endsection

@section('content')
<div class="staff__list__content__wrapper">
    <div class="staff__list__content">
        <h1 class="staff__list__header">スタッフ一覧</h1>

        <table class="staff__list__table">
            <tr class="staff__list__row">
                <th class="staff__list__label">
                    <p class="staff__list__label__name">
                        名前
                    </p>
                </th>
                <th class="staff__list__label">メールアドレス</th>
                <th class="staff__list__label">
                    <p class="staff__list__label__link">
                        月次勤怠
                    </p>
                </th>
            </tr>
            @foreach ($users as $user)
                <tr class="staff__list__row">
                    <td class="staff__list__data">
                        <p class="staff__list__data__name">
                            {{ $user -> name }}
                        </p>
                    </td>
                    <td class="staff__list__data">
                        {{ $user -> email }}
                    </td>
                    <td class="staff__list__data">
                        <div class="staff__list__data__link">
                            <a href="{{ route('admin.attendance_staff_list', ['id' => $user->id]) }}">
                                詳細
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection