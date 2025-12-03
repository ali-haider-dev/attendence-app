<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    protected $table = 'tbl_holidays';
    protected $fillable = [
        'name',
        'date',
        'description',
        'type',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    // Static Methods
    public static function isHoliday($date)
    {
        return self::where('date', $date)
            ->where('is_active', true)
            ->exists();
    }

    public static function getHolidayName($date)
    {
        $holiday = self::where('date', $date)
            ->where('is_active', true)
            ->first();
        
        return $holiday ? $holiday->name : null;
    }

    public static function getUpcomingHolidays($limit = 5)
    {
        return self::where('is_active', true)
            ->where('date', '>=', today())
            ->orderBy('date', 'asc')
            ->limit($limit)
            ->get();
    }

    // Helper Methods
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d M, Y');
    }

    public function getDaysUntilAttribute()
    {
        return today()->diffInDays($this->date, false);
    }

    public function isPast()
    {
        return $this->date->isPast();
    }
}