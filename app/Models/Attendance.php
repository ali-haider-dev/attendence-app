<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{

    protected $table = 'tbl_attendances';
    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'total_hours',
        'status',
        'scan_type',
        'device_id',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adjustment()
    {
        return $this->hasOne(AttendanceAdjustment::class);
    }

    // Helper Methods
    public function calculateTotalHours()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            $this->total_hours = round($checkIn->diffInMinutes($checkOut) / 60, 2);
            $this->save();
        }
    }

    public function determineStatus()
    {
        if (!$this->check_in) {
            return 'absent';
        }

        $user = $this->user;
        $shift = $user->shift;

        if ($shift->isLate($this->check_in)) {
            return 'late';
        }

        return 'present';
    }

    public function getFormattedCheckInAttribute()
    {
        return $this->check_in ? Carbon::parse($this->check_in)->format('h:i A') : '-';
    }

    public function getFormattedCheckOutAttribute()
    {
        return $this->check_out ? Carbon::parse($this->check_out)->format('h:i A') : '-';
    }

    public function getStatusBadgeAttribute()
    {
        // If checked in but not checked out, show "Checked In"
        if ($this->check_in && !$this->check_out) {
            return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">Checked In</span>';
        }

        $badges = [
            'present' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Present</span>',
            'absent' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Absent</span>',
            'late' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Late</span>',
            'half_day' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">Half Day</span>',
            'holiday' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Holiday</span>',
            'leave' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Leave</span>',
        ];

        return $badges[$this->status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">' . ucfirst($this->status) . '</span>';
    }

    public function getCurrentDurationAttribute()
    {
        if ($this->total_hours) {
            return number_format($this->total_hours, 2) . ' hrs';
        }

        if ($this->check_in && !$this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $now = now();
            $diffInMinutes = $checkIn->diffInMinutes($now);
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;

            return "{$hours}h {$minutes}m";
        }

        return '-';
    }

    public function canCheckOut()
    {
        return $this->check_in && !$this->check_out;
    }
}