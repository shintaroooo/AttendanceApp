<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionItem extends Model
{
    use HasFactory;

    public function correction()
    {
        return $this->belongsTo(AttendanceCorrection::class, 'attendance_correction_id');
    }

    protected $fillable = [
        'attendance_correction_id',
        'field',
        'before_value',
        'after_value',
    ];
}
