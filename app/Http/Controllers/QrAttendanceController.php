<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QrAttendanceController extends Controller
{
    /**
     * Display the QR scanner page
     */
    public function scanner()
    {
        return view('attendance.qr-scanner');
    }

    /**
     * Process QR code scan for attendance
     */
    public function scan(Request $request)
    {

        $request->validate([
            'employee_id' => 'required|string'
        ]);

        $employeeId = $request->input('employee_id');

        // Find the employee
        $employee = User::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or inactive',
                'type' => 'error'
            ], 404);
        }

        // Get today's attendance record
        $today = today();
        $attendance = Attendance::where('user_id', $employee->id)
            ->where('date', $today)
            ->first();

        $now = Carbon::now();

        // If no attendance record exists, create check-in
        if (!$attendance) {
            $attendance = $this->checkIn($employee, $now);

            return response()->json([
                'success' => true,
                'message' => "Welcome {$employee->name}! Checked in at {$now->format('h:i A')}",
                'type' => 'check_in',
                'employee' => [
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department->name ?? 'N/A'
                ],
                'time' => $now->format('h:i A'),
                'date' => $now->format('M d, Y')
            ]);
        }

        // If already checked in but not checked out
        if ($attendance->check_in && !$attendance->check_out) {
            $checkInTime = Carbon::parse($attendance->check_in);
            $minutesSinceCheckIn = $checkInTime->diffInMinutes($now);

            // Prevent accidental double scan within 5 minutes
            if ($minutesSinceCheckIn < 5) {
                return response()->json([
                    'success' => false,
                    'message' => "You already checked in at {$checkInTime->format('h:i A')}. Please wait at least 5 minutes before checking out.",
                    'type' => 'warning',
                    'employee' => [
                        'name' => $employee->name,
                        'employee_id' => $employee->employee_id,
                        'department' => $employee->department->name ?? 'N/A'
                    ],
                    'check_in_time' => $checkInTime->format('h:i A'),
                    'minutes_elapsed' => $minutesSinceCheckIn
                ], 400);
            }

            // Process check-out
            $attendance = $this->checkOut($attendance, $now);

            $totalHours = number_format($attendance->total_hours, 2);

            return response()->json([
                'success' => true,
                'message' => "Goodbye {$employee->name}! Checked out at {$now->format('h:i A')}",
                'type' => 'check_out',
                'employee' => [
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department->name ?? 'N/A'
                ],
                'check_in_time' => $checkInTime->format('h:i A'),
                'check_out_time' => $now->format('h:i A'),
                'total_hours' => $totalHours,
                'date' => $now->format('M d, Y')
            ]);
        }

        // If already checked out today
        if ($attendance->check_in && $attendance->check_out) {
            $checkOutTime = Carbon::parse($attendance->check_out);

            return response()->json([
                'success' => false,
                'message' => "You have already completed attendance for today. Check-in: {$attendance->formatted_check_in}, Check-out: {$attendance->formatted_check_out}",
                'type' => 'info',
                'employee' => [
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department->name ?? 'N/A'
                ],
                'check_in_time' => $attendance->formatted_check_in,
                'check_out_time' => $attendance->formatted_check_out,
                'total_hours' => number_format($attendance->total_hours, 2),
                'date' => $now->format('M d, Y')
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to process attendance',
            'type' => 'error'
        ], 500);
    }

    /**
     * Process check-in
     */
    private function checkIn(User $employee, Carbon $time)
    {
        $shift = $employee->shift;

        // Determine status based on shift timing
        $status = 'present';
        if ($shift && $shift->isLate($time->format('H:i:s'))) {
            $status = 'late';
        }

        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => today(),
            'check_in' => $time,
            'status' => $status,
            'scan_type' => 'qr',
            'device_id' => request()->ip()
        ]);

        return $attendance;
    }

    /**
     * Process check-out
     */
    private function checkOut(Attendance $attendance, Carbon $time)
    {
        $attendance->check_out = $time;
        $attendance->calculateTotalHours();
        $attendance->save();

        return $attendance;
    }

    /**
     * Get attendance status for an employee (optional - for debugging)
     */
    public function status($employeeId)
    {
        $employee = User::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $attendance = Attendance::where('user_id', $employee->id)
            ->where('date', today())
            ->first();

        return response()->json([
            'success' => true,
            'employee' => [
                'name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->department->name ?? 'N/A'
            ],
            'attendance' => $attendance ? [
                'check_in' => $attendance->formatted_check_in,
                'check_out' => $attendance->formatted_check_out,
                'status' => $attendance->status,
                'total_hours' => $attendance->total_hours
            ] : null
        ]);
    }
}
