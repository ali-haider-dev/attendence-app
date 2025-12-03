<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\ActivityLogger;

class LogAuthenticated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Prevent duplicate logging within 2 seconds
        $cacheKey = 'login_logged_' . $user->id;
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return;
        }

        ActivityLogger::log(
            'User Login',
            "User {$user->name} ({$user->employee_id}) logged in successfully"
        );

        // Set cache for 2 seconds to prevent duplicates
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 2);
    }
}
