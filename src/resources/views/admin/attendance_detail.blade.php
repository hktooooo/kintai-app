@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_detail.css')}}">
@endsection

@section('content')
<div class="attendance__detail__content">
    <h1 class="attendance__detail__header">勤怠詳細</h1>
    @php
        $nameParts = explode(' ', $attendance->user->name);
        $last_name = $nameParts[0] ?? '';
        $first_name = $nameParts[1] ?? '';
        [$year, $month_day] = explode(' ', $attendance->work_date_japanese);
    @endphp

    <form action="{{ route('admin.detail.correction') }}" method="post">
    @csrf
        <input type="hidden" name="id" id="id" value="{{ $attendance->id }}">
        <table class="attendance__detail__table">
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">名前</th>
                <td class="attendance__detail__data detail__name">
                    {{ $last_name }}<span>{{ $first_name }}</span>
                </td>
            </tr>
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">日付</th>
                <td class="attendance__detail__data detail__date">
                    <span class="attendance__detail__data__year">{{ $year }}</span>{{ $month_day }}
                </td>
            </tr>
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">出勤・退勤</th>
                <td>
                    <div class="detail__time">
                        <input type="text" name="clock_in" id="clock_in" value="{{ $attendance->clock_in_formatted }}" >
                        <p>～</p>
                        <input type="text" name="clock_out" id="clock_out" value="{{ $attendance->clock_out_formatted }}">
                    </div>
                </td>
            </tr>
            @foreach ($break_times as $break_time)
                <tr class="attendance__detail__row">
                    <th class="attendance__detail__label">
                        休憩
                        @if ($loop->iteration > 1)
                            {{ $loop->iteration }}
                        @endif
                    </th>
                    <td class="attendance__detail__data">
                        <input type="hidden" name="breaks[{{ $break_time->id }}][break_id]" value="{{ $break_time->id }}">
                        <div class="detail__time">
                            <input type="text" name="breaks[{{ $break_time->id }}][break_start]" value="{{ $break_time->break_start_formatted }}">
                            <p>～</p>
                            <input type="text" name="breaks[{{ $break_time->id }}][break_end]" value="{{ $break_time->break_end_formatted }}">
                        </div>
                    </td>
                </tr>
            @endforeach
            {{-- ★ 新規追加用の1行 --}}
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">
                    休憩
                    @if ($break_times->count() >= 1)
                        {{ $break_times->count() + 1 }}
                    @endif
                </th>
                <td class="attendance__detail__data">
                    <input type="hidden" name="breaks[new][break_id]" value="">
                    <div class="detail__time">
                        <input type="text" name="breaks[new][break_start]" value="">
                        <p>～</p>
                        <input type="text" name="breaks[new][break_end]" value="">
                    </div>
                </td>
            </tr>
            <tr class="attendance__detail__row">
                <th class="attendance__detail__label">備考</th>
                <td class="attendance__detail__data detail__reason">
                    <textarea name="reason" id="reason">{{ $attendance->reason }}</textarea>
                </td>
            </tr>
        </table>
        <div class="attendance__detail__status">
            <button class="attendance__detail__status__submit btn" type="submit">
                修正
            </button>
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                {{-- 出勤・退勤のエラー --}}
                @if($errors->has('clock_in'))
                    <li>{{ $errors->first('clock_in') }}</li>
                @endif
                @if($errors->has('clock_out'))
                    <li>{{ $errors->first('clock_out') }}</li>
                @endif

                {{-- 休憩のエラー --}}
                @foreach ($errors->getMessages() as $key => $messages)
                    @if (str_starts_with($key, 'breaks.'))
                        @foreach ($messages as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    @endif
                @endforeach

                {{-- 備考のエラー --}}
                @if($errors->has('reason'))
                    <li>{{ $errors->first('reason') }}</li>
                @endif
            </ul>
        </div>
    @endif
</div>
@endsection