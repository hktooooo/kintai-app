<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'clock_out' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],

            'breaks.*.break_start' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'breaks.*.break_end'   => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],

            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // --- clock_in / clock_out 片方のみ入力はエラー ---
            if ((!$clockIn && $clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間と退勤時間は両方入力する必要があります');
                return;
            }
        
            if (($clockIn && !$clockOut)) {
                $validator->errors()->add('clock_out', '出勤時間と退勤時間は両方入力する必要があります');
                return;
            }

            // 両方未入力なら休憩もチェック不要
            if (!$clockIn && !$clockOut) {
                $validator->errors()->add('clock_in', '出勤時間と退勤時間を入力してください');   
                return;
            }

            $clockInM  = $this->toMinutes($clockIn);
            $clockOutM = $this->toMinutes($clockOut);

            if ($clockInM === null || $clockOutM === null) {
                $validator->errors()->add('clock_in', '出勤時間または退勤時間が不適切な値です');
                $validator->errors()->add('clock_out', '出勤時間または退勤時間が不適切な値です');
                return;
            }

            if ($clockInM > $clockOutM) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);

            // break_startで昇順ソート
            usort($breaks, function ($a, $b) {
                $aStart = isset($a['break_start']) ? $this->toMinutes($a['break_start']) ?? PHP_INT_MAX : PHP_INT_MAX;
                $bStart = isset($b['break_start']) ? $this->toMinutes($b['break_start']) ?? PHP_INT_MAX : PHP_INT_MAX;
                return $aStart <=> $bStart;
            });

            $previousEnd = $clockInM;

            foreach ($breaks as $index => $break) {

                $start = $break['break_start'] ?? null;
                $end   = $break['break_end'] ?? null;

                // --- break の片方だけ入力はエラー ---
                if (($start && !$end) || (!$start && $end)) {
                    $validator->errors()->add(
                        "breaks.$index.break_start",
                        "休憩は開始と終了を両方入力する必要があります"
                    );
                    continue;
                }

                if ($start && $end) {
                    $startM = $this->toMinutes($start);
                    $endM   = $this->toMinutes($end);

                    // nullチェック（形式不正の可能性）
                    if ($startM === null || $endM === null) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            "休憩時間が不適切な値です"
                        );
                        continue;
                    }

                    // 休憩内の順序
                    if ($startM > $endM) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            "休憩時間が不適切な値です"
                        );
                    }

                    // 勤務時間内チェック1
                    if ($startM < $clockInM || $startM > $clockOutM) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            "休憩時間が不適切な値です"
                        );
                    }

                    // 勤務時間内チェック2
                    if ($startM > $endM) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            "休憩時間が不適切な値です"
                        );
                    }

                    // 勤務時間内チェック3
                    if ($startM > $clockInM && $endM > $clockOutM) {
                        $validator->errors()->add(
                            "breaks.$index.break_end",
                            "休憩時間もしくは退勤時間が不適切な値です"
                        );
                    }

                    // 重複チェック
                    if ($startM < $previousEnd) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            "休憩時間が不適切な値です"
                        );
                    }

                    $previousEnd = $endM;
                }
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in.regex'  => '出勤時間は00:00で入力してください',
            'clock_out.regex' => '退勤時間は00:00で入力してください',
            'breaks.*.break_start.regex' => '休憩時間は00:00で入力してください',
            'breaks.*.break_end.regex'   => '休憩時間は00:00で入力してください',
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }

    private function toMinutes(?string $time): ?int
    {
        if (empty($time)) {
            return null;
        }

        $parts = explode(':', $time);

        if (count($parts) !== 2) {
            return null;
        }

        return ((int)$parts[0]) * 60 + ((int)$parts[1]);
    }

    private function num($index)
    {
        // 休憩1の場合は空文字、2以降は番号を返す
        return $index === 0 ? '' : $index + 1;
    }
}
