<?php
namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);

        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        return view('holidays.index', compact('holidays', 'year'));
    }

    public function create()
    {
        return view('holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            // 'type' => 'required|in:public,optional',
        ]);

        $holiday = Holiday::create($validated);

        // Mark holiday for all active employees
        $this->markHolidayAttendance($holiday->date, $holiday->name);

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday created successfully!');
    }

    public function edit(Holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'type' => 'required|in:public,optional',
            'is_active' => 'boolean'
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday updated successfully!');
    }

    public function destroy(Holiday $holiday)
    {
        // Remove holiday attendances
        Attendance::where('date', $holiday->date)
            ->where('status', 'holiday')
            ->delete();

        $holiday->delete();

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday deleted successfully!');
    }

    protected function markHolidayAttendance($date, $name)
    {
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date
                ],
                [
                    'status' => 'holiday',
                    'scan_type' => 'manual',
                    'notes' => $name
                ]
            );
        }
    }
}
