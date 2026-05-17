<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    //最新の申請
    public function latestCorrection()
    {
        return $this->hasOne(AttendanceCorrection::class)->latestOfMany();
    }

    protected $fillable = [
        'user_id',
        'work_date',
        'status',
        'clock_in_at',
        'clock_out_at',
    ];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    //休憩合計時間(分)
    public function getBreakMinutesAttribute()
    {
        return $this->breakTimes->sum(function ($break) {
            if (!$break->end_at) return 0;

            return Carbon::parse($break->start_at)->diffInMinutes(Carbon::parse($break->end_at));
        });
    }
    //勤務合計時間(分)
    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in_at || !$this->clock_out_at) {
            return 0;
        }
        $workMinutes = Carbon::parse($this->clock_in_at)
            ->diffInMinutes(Carbon::parse($this->clock_out_at));

        return $workMinutes - $this->break_minutes;
    }
    //日付
    public function getWorkDateCarbonAttribute()
    {
        return Carbon::parse($this->work_date);
    }
}
