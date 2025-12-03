<?php

// ============================================
// app/Models/User.php
// ============================================

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_users';

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'phone',
        'department_id',
        'shift_id',
        'role',
        'biometric_id',
        'is_active',
        'joining_date'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'joining_date' => 'date',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceAdjustments()
    {
        return $this->hasMany(AttendanceAdjustment::class);
    }

    public function approvedAdjustments()
    {
        return $this->hasMany(AttendanceAdjustment::class, 'approved_by');
    }

    // Helper Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->employee_id})";
    }

    public function getTodayAttendance()
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->first();
    }

    public function getMonthlyStats($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $attendances = $this->attendances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'holidays' => $attendances->where('status', 'holiday')->count(),
            'leaves' => $attendances->where('status', 'leave')->count(),
            'total_hours' => round($attendances->sum('total_hours'), 2),
        ];
    }
}