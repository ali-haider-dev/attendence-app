<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'employee') {
            return redirect()->route('attendance.user', $user->id);
        }

        $date = $request->input('date', today()->format('Y-m-d'));

        $attendances = Attendance::with(['user.department'])
            ->whereDate('date', $date)
            ->orderBy('check_in', 'asc')
            ->get();

        $totalEmployees = User::where('is_active', true)
            ->where('role', 'employee')
            ->count();

        $presentCount = $attendances->whereIn('status', ['present', 'late'])->count();
        $absentCount = $totalEmployees - $presentCount;

        return view('attendance.index', compact(
            'attendances',
            'date',
            'totalEmployees',
            'presentCount',
            'absentCount'
        ));
    }

    public function userAttendance(Request $request, User $user)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $attendances = $user->attendances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $stats = $user->getMonthlyStats($month, $year);

        return view('attendance.user', compact('user', 'attendances', 'stats', 'month', 'year'));
    }

    public function markManual(Request $request)
    {
        $searchedUser = null;

        if ($request->has('employee_id')) {
            $searchedUser = User::where('employee_id', $request->employee_id)
                ->where('role', 'employee')
                ->first();

            if (!$searchedUser) {
                return redirect()->route('attendance.manual')
                    ->with('error', 'Employee not found with ID: ' . $request->employee_id);
            }
        }

        return view('attendance.manual', compact('searchedUser'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:tbl_users,id',
            'date' => 'required|date|before_or_equal:today',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'notes' => 'nullable|string'
        ]);

        $checkIn = Carbon::parse($validated['date'] . ' ' . $validated['check_in']);
        $checkOut = isset($validated['check_out'])
            ? Carbon::parse($validated['date'] . ' ' . $validated['check_out'])
            : null;

        $totalHours = $checkOut ? $checkIn->diffInMinutes($checkOut) / 60 : null;

        Attendance::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'date' => $validated['date']
            ],
            [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_hours' => $totalHours,
                'status' => $validated['status'],
                'scan_type' => 'manual',
                'notes' => $validated['notes'] ?? null
            ]
        );

        // Log the activity
        $targetUser = User::find($validated['user_id']);
        \App\Services\ActivityLogger::log(
            'Manual Attendance',
            "Marked attendance for {$targetUser->name} ({$targetUser->employee_id}) on {$validated['date']}"
        );

        return redirect()->route('attendance.index')
            ->with('success', 'Manual attendance marked successfully!');
    }
}