<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'working_hours',
        'status',
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
}