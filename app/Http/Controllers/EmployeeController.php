<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Department;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department', 'shift']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $employees = $query->paginate(15)->withQueryString(); // Keeps search/filters in pagination
        $departments = Department::where('is_active', true)->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('employees.create', compact('departments', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:tbl_users,employee_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'department_id' => 'required|exists:tbl_departments,id',
            'shift_id' => 'required|exists:tbl_shifts,id',
            'role' => 'required|in:admin,employee',
            'biometric_id' => 'nullable|unique:tbl_users,biometric_id',
            'joining_date' => 'required|date',

            // Profile
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
        ]);

        // Create user
        $user = User::create([
            'employee_id' => $validated['employee_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'shift_id' => $validated['shift_id'],
            'role' => $validated['role'],
            'biometric_id' => $validated['biometric_id'] ?? null,
            'joining_date' => $validated['joining_date'],
            'is_active' => true,
        ]);

        // Create profile
        Profile::create([
            'user_id' => $user->id,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
        ]);

        \App\Services\ActivityLogger::log(
            'Create Employee',
            "Created new employee: {$user->name} ({$user->employee_id})"
        );

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully!');
    }

    public function show(User $employee)
    {
        $employee->load(['department', 'shift', 'profile']);

        // Get monthly stats
        $monthlyStats = $employee->getMonthlyStats();

        // Recent attendances
        $recentAttendances = $employee->attendances()
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        return view('employees.show', compact('employee', 'monthlyStats', 'recentAttendances'));
    }

    public function edit(User $employee)
    {
        $departments = Department::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('employees.edit', compact('employee', 'departments', 'shifts'));
    }

    public function update(Request $request, User $employee)
    {

        $validated = $request->validate([
            'employee_id' => 'required|unique:tbl_users,employee_id,' . $employee->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_users,email,' . $employee->id,
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'department_id' => 'required|exists:tbl_departments,id',
            'shift_id' => 'required|exists:tbl_shifts,id',
            'role' => 'required|in:admin,employee',
            'biometric_id' => 'nullable|unique:tbl_users,biometric_id,' . $employee->id,


            // Profile
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
        ]);

        // Update user
        $userData = [
            'employee_id' => $validated['employee_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'shift_id' => $validated['shift_id'],
            'role' => $validated['role'],
            'biometric_id' => $validated['biometric_id'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $employee->update($userData);

        // Update profile
        $employee->profile->update([
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
        ]);

        \App\Services\ActivityLogger::log(
            'Update Employee',
            "Updated employee: {$employee->name} ({$employee->employee_id})"
        );

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $employee->delete();

        \App\Services\ActivityLogger::log(
            'Delete Employee',
            "Deleted employee: {$employee->name} ({$employee->employee_id})"
        );

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully!');
    }
}
