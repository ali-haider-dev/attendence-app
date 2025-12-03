<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    protected $table = 'tbl_shifts';
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_period',
        'working_hours',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Helper Methods
    public function getFormattedStartTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('h:i A');
    }

    public function getFormattedEndTimeAttribute()
    {
        return Carbon::parse($this->end_time)->format('h:i A');
    }

    public function getGraceEndTime()
    {
        return Carbon::parse($this->start_time)->addMinutes($this->grace_period);
    }

    public function isLate($checkInTime)
    {
        $checkIn = Carbon::parse($checkInTime);
        $graceEnd = $this->getGraceEndTime();
        
        return $checkIn->greaterThan($graceEnd);
    }
}