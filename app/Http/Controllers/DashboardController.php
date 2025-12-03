<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceAdjustment;
use App\Models\Holiday;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->employeeDashboard();
    }

    protected function adminDashboard()
    {
        $today = today();

        // Today's attendance summary
        $todayAttendance = Attendance::with('user')
            ->whereDate('date', $today)
            ->get();

        $totalEmployees = User::where('is_active', true)
            ->where('role', 'employee')
            ->count();

        $presentToday = $todayAttendance->whereIn('status', ['present', 'late'])->count();
        $absentToday = $totalEmployees - $presentToday;
        $lateToday = $todayAttendance->where('status', 'late')->count();

        // Pending adjustment requests
        $pendingAdjustments = AttendanceAdjustment::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Upcoming holidays
        $upcomingHolidays = Holiday::getUpcomingHolidays(3);

        // Recent attendances
        $recentAttendances = Attendance::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalEmployees',
            'presentToday',
            'absentToday',
            'lateToday',
            'pendingAdjustments',
            'upcomingHolidays',
            'recentAttendances'
        ));
    }

    protected function employeeDashboard()
    {
        $user = auth()->user();
        
        // Today's attendance
        $todayAttendance = $user->getTodayAttendance();

        // This month stats
        $monthlyStats = $user->getMonthlyStats();

        // Recent attendances
        $recentAttendances = $user->attendances()
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        // Pending adjustment requests
        $pendingRequests = $user->attendanceAdjustments()
            ->where('status', 'pending')
            ->count();

        // Upcoming holidays
        $upcomingHolidays = Holiday::getUpcomingHolidays(3);

        // Calendar data for current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Get all attendances for current month
        $monthlyAttendances = $user->attendances()
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // Get holidays for current month
        $monthlyHolidays = Holiday::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // Calendar info
        $calendarData = [
            'month' => $currentMonth,
            'year' => $currentYear,
            'monthName' => now()->format('F Y'),
            'firstDay' => \Carbon\Carbon::create($currentYear, $currentMonth, 1)->dayOfWeek,
            'daysInMonth' => \Carbon\Carbon::create($currentYear, $currentMonth)->daysInMonth,
            'attendances' => $monthlyAttendances,
            'holidays' => $monthlyHolidays,
        ];

        return view('dashboard.employee', compact(
            'user',
            'todayAttendance',
            'monthlyStats',
            'recentAttendances',
            'pendingRequests',
            'upcomingHolidays',
            'calendarData'
        ));
    }
}