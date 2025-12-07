<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'break_hours',
        'break_seconds',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function break_corrections()
    {
        return $this->hasMany(BreakCorrection::class);
    }

    // 日付キャスト
    protected $casts = [
        'break_start' => 'datetime:H:i:s',
        'break_end' => 'datetime:H:i:s',
    ];

    public function getBreakStartFormattedAttribute()
    {
        return $this->break_start
            ? $this->break_start->format('H:i')
            : null;
    }

    public function getBreakEndFormattedAttribute()
    {
        return $this->break_end
            ? $this->break_end->format('H:i')
            : null;
    }
}