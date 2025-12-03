<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Services\ActivityLogger;

class LogLogout
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
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            // Prevent duplicate logging within 2 seconds
            $cacheKey = 'logout_logged_' . $user->id;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return;
            }

            ActivityLogger::log(
                'User Logout',
                "User {$user->name} ({$user->employee_id}) logged out"
            );

            // Set cache for 2 seconds to prevent duplicates
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 2);
        }
    }
}
