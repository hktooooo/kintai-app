<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
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
}