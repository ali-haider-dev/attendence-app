<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register admin middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Sync attendance from biometric device every 5 minutes
        $schedule->call(function () {
            app(\App\Services\BiometricService::class)->syncAttendanceLogs();
        })->everyFiveMinutes();

        // Mark absent for yesterday at 12:30 AM
        $schedule->call(function () {
            $yesterday = \Carbon\Carbon::yesterday()->format('Y-m-d');
            
            // Skip if yesterday was a holiday
            if (\App\Models\Holiday::isHoliday($yesterday)) {
                return;
            }

            $users = \App\Models\User::where('is_active', true)->get();
            
            foreach ($users as $user) {
                $exists = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereDate('date', $yesterday)
                    ->exists();

                if (!$exists) {
                    \App\Models\Attendance::create([
                        'user_id' => $user->id,
                        'date' => $yesterday,
                        'status' => 'absent',
                        'scan_type' => 'manual',
                        'notes' => 'Auto marked absent'
                    ]);
                }
            }
        })->dailyAt('00:30');

        // Auto mark upcoming holidays
        $schedule->call(function () {
            $upcomingHolidays = \App\Models\Holiday::where('is_active', true)
                ->whereDate('date', '>=', now())
                ->whereDate('date', '<=', now()->addDays(7))
                ->get();

            foreach ($upcomingHolidays as $holiday) {
                $users = \App\Models\User::where('is_active', true)->get();
                
                foreach ($users as $user) {
                    \App\Models\Attendance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $holiday->date
                        ],
                        [
                            'status' => 'holiday',
                            'scan_type' => 'manual',
                            'notes' => $holiday->name
                        ]
                    );
                }
            }
        })->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
