<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Profile;
use App\Models\Shift;
use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ----------------------------
        // Create Departments
        // ----------------------------
        $hr = Department::create([
            'name' => 'HR',
            'description' => 'HR Department',
            'is_active' => true,
        ]);

        $it = Department::create([
            'name' => 'IT',
            'description' => 'IT Department',
            'is_active' => true,
        ]);

        $sales = Department::create([
            'name' => 'Sales',
            'description' => 'Sales Department',
            'is_active' => true,
        ]);

        $xolva = Department::create([
            'name' => 'Xolva',
            'description' => 'Xolva Department',
            'is_active' => true,
        ]);

        // ----------------------------
        // Create Shifts
        // ----------------------------
        $morningShift = Shift::create([
            'name' => 'Morning Shift',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period' => 15,
            'working_hours' => 8,
            'is_active' => true,
        ]);

        $eveningShift = Shift::create([
            'name' => 'Evening Shift',
            'start_time' => '14:00',
            'end_time' => '22:00',
            'grace_period' => 15,
            'working_hours' => 8,
            'is_active' => true,
        ]);

        // ----------------------------
        // Create Admin User
        // ----------------------------
        $admin = User::create([
            'employee_id' => 'EMP001',
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'phone' => '03001234567',
            'department_id' => $hr->id,
            'shift_id' => $morningShift->id,
            'role' => 'admin',
            'biometric_id' => '1',
            'is_active' => true,
            'joining_date' => now()->subYears(2),
        ]);

        Profile::create([
            'user_id' => $admin->id,
            'gender' => 'male',
            'city' => 'Karachi',
        ]);

        // ----------------------------
        // Create Employee 1
        // ----------------------------
        $employee1 = User::create([
            'employee_id' => 'EMP002',
            'name' => 'Ali haider',
            'email' => 'ali@gmail.com',
            'password' => Hash::make('Ali@1234'),
            'phone' => '03001234568',
            'department_id' => $xolva->id,
            'shift_id' => $morningShift->id,
            'role' => 'employee',
            'biometric_id' => '2',
            'is_active' => true,
            'joining_date' => now()->subYear(),
        ]);

        Profile::create([
            'user_id' => $employee1->id,
            'gender' => 'male',
            'city' => 'Karachi',
        ]);

        // ----------------------------
        // Create Employee 2
        // ----------------------------
        $employee2 = User::create([
            'employee_id' => 'EMP003',
            'name' => 'Ashir Azeem',
            'email' => 'ashir@gmail.com',
            'password' => Hash::make('Ashir@123'),
            'phone' => '03001234569',
            'department_id' => $xolva->id,
            'shift_id' => $morningShift->id,
            'role' => 'employee',
            'biometric_id' => '3',
            'is_active' => true,
            'joining_date' => now()->subMonths(6),
        ]);

        Profile::create([
            'user_id' => $employee2->id,
            'gender' => 'male',
            'city' => 'Karachi',
        ]);

        // ----------------------------
        // Output
        // ----------------------------
        $this->command->info('Seeding completed!');
        $this->command->info('Admin: admin@company.com / password123');
        $this->command->info('Employee1: john@company.com / password123');
        $this->command->info('Employee2: jane@company.com / password123');
    }
}
