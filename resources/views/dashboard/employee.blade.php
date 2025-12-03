<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold">Welcome back, {{ $user->name }}! 👋</h3>
                    <p class="mt-2 text-indigo-100">{{ $user->department->name ?? 'N/A' }} •
                        {{ $user->shift->name ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Today's Attendance -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Today's Attendance</h3>
                    @if($todayAttendance)
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Check In</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $todayAttendance->formatted_check_in }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Check Out</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $todayAttendance->formatted_check_out }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Hours</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $todayAttendance->current_duration }}
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                <div class="mt-1">{!! $todayAttendance->status_badge !!}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-4 text-gray-500 dark:text-gray-400">No attendance marked for today</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Monthly Stats -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Present</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $monthlyStats['present'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Absent</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $monthlyStats['absent'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Late</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $monthlyStats['late'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Leaves</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $monthlyStats['leaves'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Hours</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $monthlyStats['total_hours'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Attendance Graph -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg"> <!-- Removed overflow-hidden -->
                <div class="p-6">
                    <!-- Legend -->
                    <div class="flex flex-wrap items-center gap-4 mb-8 text-xs justify-center sm:justify-start">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-blue-600 rounded-sm"></div>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Present</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-yellow-400 rounded-sm"></div>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Late</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-red-600 rounded-sm"></div>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Absent</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-[#fef08a] border border-gray-200 rounded-sm"></div>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Leave</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-gray-900 dark:bg-black rounded-sm"></div>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">OFF/Weekend</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-purple-600 rounded-sm"></div>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Holiday</span>
                        </div>
                    </div>

                    <!-- Graph Container -->
                    <div class="relative w-full overflow-x-auto pb-12 pt-12"> <!-- Added pt-12 for tooltip space -->
                        <!-- Fixed Height Container -->
                        <div class="h-72 min-w-[900px] relative mt-4 ml-12 mr-4">

                            <!-- Y-Axis Grid Lines & Labels -->
                            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none z-0">
                                <div class="w-full border-t border-gray-200 dark:border-gray-700 relative">
                                    <span class="absolute -top-2.5 -left-8 text-xs text-gray-500 font-mono">12:00</span>
                                </div>
                                <div class="w-full border-t border-gray-100 dark:border-gray-700 relative">
                                    <span class="absolute -top-2.5 -left-8 text-xs text-gray-500 font-mono">09:00</span>
                                </div>
                                <div class="w-full border-t border-gray-100 dark:border-gray-700 relative">
                                    <span class="absolute -top-2.5 -left-8 text-xs text-gray-500 font-mono">06:00</span>
                                </div>
                                <div class="w-full border-t border-gray-100 dark:border-gray-700 relative">
                                    <span class="absolute -top-2.5 -left-8 text-xs text-gray-500 font-mono">03:00</span>
                                </div>
                                <div class="w-full border-t border-gray-300 dark:border-gray-600 relative">
                                    <span class="absolute -top-2.5 -left-8 text-xs text-gray-500 font-mono">00:00</span>
                                </div>
                            </div>

                            <!-- Bars Container -->
                            <div class="absolute inset-0 flex items-end justify-between gap-2 pl-2 z-10">
                                @for($day = 1; $day <= $calendarData['daysInMonth']; $day++)
                                    @php
                                        $date = \Carbon\Carbon::create($calendarData['year'], $calendarData['month'], $day);
                                        $dateKey = $date->format('Y-m-d');
                                        $isToday = $date->isToday();
                                        $isFuture = $date->isFuture();
                                        $isWeekend = $date->isWeekend();

                                        $attendance = $calendarData['attendances'][$dateKey] ?? null;
                                        $holiday = $calendarData['holidays'][$dateKey] ?? null;

                                        $hours = $attendance->total_hours ?? 0;
                                        $heightPercentage = 0;
                                        $barColor = 'bg-gray-100 dark:bg-gray-800'; // Default

                                        if ($holiday) {
                                            $barColor = 'bg-purple-600';
                                            $heightPercentage = 20;
                                        } elseif ($attendance) {
                                            $heightPercentage = min(($hours / 12) * 100, 100);

                                            if ($attendance->status == 'present')
                                                $barColor = 'bg-blue-600';
                                            elseif ($attendance->status == 'late')
                                                $barColor = 'bg-yellow-400';
                                            elseif ($attendance->status == 'half_day')
                                                $barColor = 'bg-teal-500';
                                            elseif ($attendance->status == 'leave') {
                                                $barColor = 'bg-[#fef08a]';
                                                $heightPercentage = 20;
                                            } elseif ($attendance->status == 'absent') {
                                                $barColor = 'bg-red-600';
                                                $heightPercentage = 20;
                                            }
                                        } else {
                                            // No Record
                                            if ($isWeekend) {
                                                $barColor = 'bg-gray-900 dark:bg-black';
                                                $heightPercentage = 20;
                                            } elseif (!$isFuture) {
                                                // Absent (Weekday Past)
                                                $barColor = 'bg-red-600';
                                                $heightPercentage = 20;
                                            }
                                        }

                                        // Ensure visibility for debug if 0
                                        $displayHeight = $heightPercentage > 0 ? $heightPercentage : 2;
                                        if ($heightPercentage == 0 && $isFuture) {
                                            $displayHeight = 0.5; // Tiny line for future
                                        }
                                    @endphp

                                    <div class="relative flex-1 h-full flex items-end group">
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-50 w-max">
                                            <div
                                                class="bg-gray-900 text-white text-xs rounded py-1 px-2 shadow-lg text-center">
                                                <div class="font-bold">{{ $date->format('D, M d') }}</div>
                                                @if($holiday)
                                                    <div class="text-purple-300">{{ $holiday->name }}</div>
                                                @elseif($attendance)
                                                    <div>{{ $attendance->formatted_check_in }} -
                                                        {{ $attendance->formatted_check_out }}</div>
                                                    <div>{{ number_format($hours, 2) }} hrs</div>
                                                    <div class="capitalize text-gray-300">{{ $attendance->status }}</div>
                                                @elseif($isWeekend)
                                                    <div class="text-gray-400">OFF / Weekend</div>
                                                @else
                                                    <div class="text-red-400">Absent</div>
                                                @endif
                                            </div>
                                            <div class="w-2 h-2 bg-gray-900 transform rotate-45 mx-auto -mt-1"></div>
                                        </div>

                                        <!-- The Bar -->
                                        <div class="w-full rounded-t-sm transition-all duration-300 hover:opacity-80 {{ $barColor }}"
                                            style="height: {{ $displayHeight }}%;">
                                        </div>

                                        <!-- X-Axis Label -->
                                        <div
                                            class="absolute -bottom-12 left-1/2 transform -translate-x-1/2 -rotate-45 origin-top-left w-max">
                                            <span
                                                class="text-[10px] text-gray-500 dark:text-gray-400 font-medium block mt-1">
                                                {{ $date->format('d-M') }}
                                            </span>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Attendance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Attendance (Last 7
                                Days)</h3>
                            <a href="{{ route('attendance.user', $user) }}"
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-sm font-medium">View
                                All →</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($recentAttendances as $attendance)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $attendance->date->format('M d, Y') }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->formatted_check_in }} - {{ $attendance->formatted_check_out }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        {!! $attendance->status_badge !!}
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $attendance->total_hours ? number_format($attendance->total_hours, 2) . ' hrs' : '-' }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent attendance records
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Upcoming Holidays & Pending Requests -->
                <div class="space-y-6">
                    <!-- Pending Requests -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pending Requests</h3>
                                <a href="{{ route('adjustments.index') }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-sm font-medium">View
                                    All →</a>
                            </div>
                            @if($pendingRequests > 0)
                                <div
                                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                                You have {{ $pendingRequests }} pending adjustment
                                                {{ $pendingRequests > 1 ? 'requests' : 'request' }}
                                            </p>
                                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                                Waiting for admin approval
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No pending requests</p>
                            @endif
                        </div>
                    </div>

                    <!-- Upcoming Holidays -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Upcoming Holidays
                            </h3>
                            <div class="space-y-3">
                                @forelse($upcomingHolidays as $holiday)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $holiday->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $holiday->date->format('M d, Y') }}</p>
                                        </div>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Holiday</span>
                                    </div>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No upcoming holidays</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>