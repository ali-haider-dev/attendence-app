<?php
namespace App\Http\Controllers;

use App\Models\AttendanceAdjustment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdjustmentController extends Controller
{
    // Employee: View own adjustment requests
    public function myRequests()
    {
        $requests = auth()->user()
            ->attendanceAdjustments()
            ->with('attendance')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('adjustments.my-requests', compact('requests'));
    }

    // Employee: Create adjustment request
    public function create()
    {
        $attendances = auth()->user()->attendances()
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        return view('adjustments.create', compact('attendances'));
    }

    // Employee: Store adjustment request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'requested_check_in' => 'nullable|date_format:H:i',
            'requested_check_out' => 'nullable|date_format:H:i|after:requested_check_in',
            'reason' => 'required|string|min:10'
        ]);

        $userId = auth()->id();

        // Check for pending request
        $existing = AttendanceAdjustment::where('user_id', $userId)
            ->where('date', $validated['date'])
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have a pending request for this date!');
        }

        // Get attendance if exists
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $validated['date'])
            ->first();

        AttendanceAdjustment::create([
            'user_id' => $userId,
            'attendance_id' => $attendance?->id,
            'date' => $validated['date'],
            'requested_check_in' => $validated['requested_check_in'] ?? null,
            'requested_check_out' => $validated['requested_check_out'] ?? null,
            'reason' => $validated['reason'],
            'status' => 'pending'
        ]);

        \App\Services\ActivityLogger::log(
            'Adjustment Request',
            "Submitted adjustment request for {$validated['date']}"
        );

        return redirect()->route('adjustments.index')
            ->with('success', 'Adjustment request submitted successfully!');
    }

    // Admin: View all adjustment requests
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = AttendanceAdjustment::with(['user.department', 'approver']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $adjustments = $query->orderBy('created_at', 'desc')->get();

        return view('adjustments.index', compact('adjustments', 'status'));
    }

    // Admin: Process adjustment request
    public function process(Request $request, AttendanceAdjustment $adjustment)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        if (!$adjustment->isPending()) {
            return back()->with('error', 'This request has already been processed!');
        }

        $adjustment->update([
            'status' => $validated['status'],
            'approved_by' => auth()->id(),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'approved_at' => now()
        ]);

        // If approved, update attendance
        if ($validated['status'] === 'approved') {
            $checkIn = $adjustment->requested_check_in
                ? Carbon::parse($adjustment->date . ' ' . $adjustment->requested_check_in)
                : null;

            $checkOut = $adjustment->requested_check_out
                ? Carbon::parse($adjustment->date . ' ' . $adjustment->requested_check_out)
                : null;

            $totalHours = ($checkIn && $checkOut)
                ? $checkIn->diffInMinutes($checkOut) / 60
                : null;

            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $adjustment->user_id,
                    'date' => $adjustment->date
                ],
                [
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'total_hours' => $totalHours,
                    'status' => 'present',
                    'scan_type' => 'adjustment',
                    'notes' => 'Adjusted by admin'
                ]
            );

            $adjustment->update(['attendance_id' => $attendance->id]);
        }

        $message = $validated['status'] === 'approved'
            ? 'Request approved successfully!'
            : 'Request rejected!';

        return back()->with('success', $message);
    }

    // Employee: Delete own pending request
    public function destroy(AttendanceAdjustment $adjustment)
    {
        if ($adjustment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        if (!$adjustment->isPending()) {
            return back()->with('error', 'Cannot delete processed requests!');
        }

        $adjustment->delete();

        return back()->with('success', 'Request deleted successfully!');
    }

    // Admin: Approve adjustment request
    public function approve(AttendanceAdjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'This request has already been processed!');
        }

        $adjustment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // Update attendance
        $checkIn = $adjustment->requested_check_in
            ? Carbon::parse($adjustment->date . ' ' . $adjustment->requested_check_in)
            : null;

        $checkOut = $adjustment->requested_check_out
            ? Carbon::parse($adjustment->date . ' ' . $adjustment->requested_check_out)
            : null;

        $totalHours = ($checkIn && $checkOut)
            ? $checkIn->diffInMinutes($checkOut) / 60
            : null;

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $adjustment->user_id,
                'date' => $adjustment->date
            ],
            [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_hours' => $totalHours,
                'status' => 'present',
                'scan_type' => 'adjustment',
                'notes' => 'Adjusted by admin'
            ]
        );

        $adjustment->update(['attendance_id' => $attendance->id]);

        \App\Services\ActivityLogger::log(
            'Adjustment Approved',
            "Approved adjustment request for {$adjustment->user->name} ({$adjustment->user->employee_id}) on {$adjustment->date}"
        );

        return back()->with('success', 'Request approved successfully!');
    }

    // Admin: Reject adjustment request
    public function reject(AttendanceAdjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'This request has already been processed!');
        }

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        \App\Services\ActivityLogger::log(
            'Adjustment Rejected',
            "Rejected adjustment request for {$adjustment->user->name} ({$adjustment->user->employee_id}) on {$adjustment->date}"
        );

        return back()->with('success', 'Request rejected!');
    }
}