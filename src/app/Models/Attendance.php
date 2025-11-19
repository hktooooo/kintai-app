<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'working_hours',
        'total_break',
        'status',
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 1つの勤怠に複数の休憩
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    // 日付キャスト
    protected $casts = [
        'work_date' => 'datetime',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    // アクセサ（Y/m/d に整形）
    public function getWorkDateFormattedAttribute()
    {
        return $this->work_date
            ? $this->work_date->format('Y/m/d')
            : null;
    }

    public function getWorkDateJapaneseAttribute()
    {
        return $this->work_date
            ? $this->work_date->format('Y年 n月j日')
            : null;
    }

    public function getClockInFormattedAttribute()
    {
        return $this->clock_in
            ? $this->clock_in->format('H:i')
            : null;
    }

    public function getClockOutFormattedAttribute()
    {
        return $this->clock_out
            ? $this->clock_out->format('H:i')
            : null;
    }
}