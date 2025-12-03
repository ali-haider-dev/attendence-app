<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Attendance Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideIn {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div
                class="inline-block bg-gradient-to-br from-indigo-600 to-purple-700 text-white px-6 py-3 rounded-full shadow-lg mb-4">
                <h1 class="text-2xl font-bold">QR Attendance System</h1>
            </div>
            <p class="text-gray-600 text-lg">Scan your employee ID card to mark attendance</p>
        </div>

        <!-- Status Messages -->
        <div id="messageContainer" class="max-w-2xl mx-auto mb-6"></div>

        <!-- Scanner Container -->
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white p-6">
                <h2 class="text-xl font-semibold flex items-center justify-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                        </path>
                    </svg>
                    Attendance Scanner
                </h2>
            </div>

            <div class="p-8">
                <!-- Scanner Ready Indicator -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2 pulse"></div>
                        <span class="font-semibold">Scanner Ready</span>
                    </div>
                </div>

                <!-- Main Input Form -->
                <form id="scanForm" class="space-y-4">
                    <div>
                        <label for="employeeId" class="block text-sm font-medium text-gray-700 mb-2">
                            Employee ID
                        </label>
                        <input type="text" id="employeeId" name="employeeId" placeholder="Scan or enter Employee ID"
                            autocomplete="off" autofocus
                            class="w-full px-6 py-4 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>

                    <div class="text-center text-sm text-gray-500">
                        <p>Scan your ID card or type your Employee ID and press Enter</p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Time Display -->
        <div class="max-w-2xl mx-auto mt-6 text-center">
            <div class="inline-block bg-white rounded-lg shadow-md px-6 py-3">
                <p class="text-sm text-gray-600">Current Time</p>
                <p id="currentTime" class="text-2xl font-bold text-indigo-600"></p>
                <p id="currentDate" class="text-sm text-gray-500 mt-1"></p>
            </div>
        </div>

        <!-- Instructions -->
        <div class="max-w-2xl mx-auto mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">How to use:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>First scan of the day = Check-In</li>
                        <li>Second scan of the day = Check-Out</li>
                        <li>Wait at least 5 minutes between scans</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const employeeIdInput = document.getElementById('employeeId');
        const scanForm = document.getElementById('scanForm');

        // Auto-focus on input field
        function resetFocus() {
            employeeIdInput.value = '';
            employeeIdInput.focus();
        }

        // Form submission
        scanForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const employeeId = employeeIdInput.value.trim();

            if (employeeId) {
                processAttendance(employeeId);
            }
        });

        // Process attendance
        async function processAttendance(employeeId) {
            try {
                const response = await fetch('/qr-attendance/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ employee_id: employeeId })
                });

                const data = await response.json();

                if (data.success) {
                    if (data.type === 'check_in') {
                        showSuccessCard(data, 'check_in');
                    } else if (data.type === 'check_out') {
                        showSuccessCard(data, 'check_out');
                    }
                } else {
                    showMessage(data.message, data.type || 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            } finally {

                setTimeout(() => { resetFocus(); }, 1000);
            }
        }

        // Show success card with employee details
        function showSuccessCard(data, type) {
            const bgColor = type === 'check_in' ? 'bg-green-50 border-green-200' : 'bg-blue-50 border-blue-200';
            const iconColor = type === 'check_in' ? 'text-green-600' : 'text-blue-600';
            const icon = type === 'check_in'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>';

            const html = `
                <div class="slide-in border-2 ${bgColor} rounded-xl p-6 shadow-lg">
                    <div class="flex items-start">
                        <div class="${iconColor} mr-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${icon}
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">${data.message}</h3>
                            <div class="space-y-1 text-gray-700">
                                <p><span class="font-semibold">Name:</span> ${data.employee.name}</p>
                                <p><span class="font-semibold">ID:</span> ${data.employee.employee_id}</p>
                                <p><span class="font-semibold">Department:</span> ${data.employee.department}</p>
                                <p><span class="font-semibold">Time:</span> ${data.time}</p>
                                ${data.total_hours ? `<p><span class="font-semibold">Total Hours:</span> ${data.total_hours} hrs</p>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const container = document.getElementById('messageContainer');
            container.innerHTML = html;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Show message
        function showMessage(message, type) {
            const colors = {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'info': 'bg-blue-50 border-blue-200 text-blue-800'
            };

            const html = `
                <div class="slide-in border-2 ${colors[type]} rounded-xl p-4 shadow-lg">
                    <p class="font-semibold">${message}</p>
                </div>
            `;

            const container = document.getElementById('messageContainer');
            container.innerHTML = html;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Update current time and date
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            const dateString = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('currentTime').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Initialize
        updateTime();
        setInterval(updateTime, 1000);
        resetFocus();

        // Keep focus on input when clicking anywhere on the page
        document.addEventListener('click', function () {
            employeeIdInput.focus();
        });

        // Refocus when window regains focus
        window.addEventListener('focus', function () {
            employeeIdInput.focus();
        });
    </script>
</body>

</html>