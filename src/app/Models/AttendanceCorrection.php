<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in_correction',
        'clock_out_correction',
        'reason_correction',
        'requested_date',
        'approval_status',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakCorrections()
    {
        return $this->hasMany(BreakCorrection::class, 'attendance_correction_id');
    }

    // 日付キャスト
    protected $casts = [
        'requested_date' => 'datetime',
        'clock_in_correction' => 'datetime',
        'clock_out_correction' => 'datetime',
    ];

    // アクセサ（Y/m/d に整形）
    public function getRequestedDateFormattedAttribute()
    {
        return $this->requested_date
            ? $this->requested_date->format('Y/m/d')
            : null;
    }

    public function getClockInCorrectionFormattedAttribute()
    {
        return $this->clock_in_correction
            ? $this->clock_in_correction->format('H:i')
            : null;
    }

    public function getClockOutCorrectionFormattedAttribute()
    {
        return $this->clock_out_correction
            ? $this->clock_out_correction->format('H:i')
            : null;
    }
}
