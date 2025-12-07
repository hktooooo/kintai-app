<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakCorrection extends Model
{
    use HasFactory;
    
    protected $table = 'break_corrections';

    protected $fillable = [
        'attendance_correction_id',
        'break_id',
        'break_start_correction',
        'break_end_correction',
    ];

    public function attendanceCorrection()
    {
        return $this->belongsTo(AttendanceCorrection::class, 'attendance_correction_id');
    }

    public function breaktime()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }

    // 日付キャスト
    protected $casts = [
        'break_start_correction' => 'datetime:H:i:s',
        'break_end_correction' => 'datetime:H:i:s',
    ];

    public function getBreakStartCorrectionFormattedAttribute()
    {
        return $this->break_start_correction
            ? $this->break_start_correction->format('H:i')
            : null;
    }

    public function getBreakEndCorrectionFormattedAttribute()
    {
        return $this->break_end_correction
            ? $this->break_end_correction->format('H:i')
            : null;
    }
}