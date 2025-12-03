<?php
namespace App\Services;

use Rats\Zkteco\Lib\ZKTeco;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BiometricService
{
    protected $device;

    public function __construct()
    {
        $this->device = new ZKTeco(
            config('biometric.device_ip'),
            config('biometric.device_port')
        );
    }

    /**
     * Connect to device
     */
    public function connect()
    {
        try {
            $this->device->connect();
            Log::info('Biometric device connected');
            return true;
        } catch (\Exception $e) {
            Log::error('Device connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync attendance from device
     * Yeh method scheduler har 5 minutes mein call karta hai
     */
    public function syncAttendanceLogs()
    {
        if (!$this->connect()) {
            return ['success' => false, 'message' => 'Connection failed'];
        }

        try {
            // Device se attendance logs fetch karo
            $logs = $this->device->getAttendance();
            $synced = 0;
            $errors = [];

            foreach ($logs as $log) {
                try {
                    // Step 1: Find user by biometric_id
                    // Device returns 'id' which is the biometric_id in our users table
                    $user = User::where('biometric_id', $log['id'])->first();
                    
                    if (!$user) {
                        $errors[] = "User not found for biometric ID: {$log['id']}";
                        continue;
                    }

                    // Step 2: Parse timestamp
                    $timestamp = Carbon::parse($log['timestamp']);
                    $date = $timestamp->format('Y-m-d');

                    // Step 3: Check if attendance exists for today
                    $attendance = Attendance::where('user_id', $user->id)
                        ->whereDate('date', $date)
                        ->first();

                    if (!$attendance) {
                        // FIRST SCAN = CHECK IN
                        $attendance = Attendance::create([
                            'user_id' => $user->id,
                            'date' => $date,
                            'check_in' => $timestamp->format('H:i:s'),
                            'status' => $this->determineStatus($user, $timestamp),
                            'scan_type' => $log['type'] == '1' ? 'fingerprint' : 'face',
                            'device_id' => config('biometric.device_id')
                        ]);
                        
                        Log::info("Check-in marked for {$user->name} at {$timestamp}");
                        $synced++;
                        
                    } elseif (!$attendance->check_out) {
                        // SECOND SCAN = CHECK OUT
                        $attendance->update([
                            'check_out' => $timestamp->format('H:i:s')
                        ]);
                        $attendance->calculateTotalHours();
                        
                        Log::info("Check-out marked for {$user->name} at {$timestamp}");
                        $synced++;
                    } else {
                        // Already checked in and out
                        Log::info("Attendance already complete for {$user->name} on {$date}");
                    }

                } catch (\Exception $e) {
                    $errors[] = "Error processing log: {$e->getMessage()}";
                    Log::error("Sync error: {$e->getMessage()}");
                }
            }

            $this->device->disconnect();

            return [
                'success' => true,
                'total_logs' => count($logs),
                'synced' => $synced,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('Sync failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Register user biometric on device
     * Admin panel se call hoga jab naya employee create karenge
     */
    public function registerBiometric($userId, $biometricId, $name = '')
    {
        if (!$this->connect()) {
            return false;
        }

        try {
            // Device pe user add karo
            // Parameters: biometric_id, user_id, name, password, role
            $this->device->setUser($biometricId, $userId, $name, '', 0);
            $this->device->disconnect();

            Log::info("Biometric registered for user ID: {$userId}, Biometric ID: {$biometricId}");
            
            // Database mein biometric_id update karo
            User::where('id', $userId)->update(['biometric_id' => $biometricId]);

            return true;
        } catch (\Exception $e) {
            Log::error('Biometric registration failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove user from device
     */
    public function removeBiometric($biometricId)
    {
        if (!$this->connect()) {
            return false;
        }

        try {
            $this->device->deleteUser($biometricId);
            $this->device->disconnect();
            return true;
        } catch (\Exception $e) {
            Log::error('Remove biometric failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users from device
     */
    public function getDeviceUsers()
    {
        if (!$this->connect()) {
            return [];
        }

        try {
            $users = $this->device->getUser();
            $this->device->disconnect();
            return $users;
        } catch (\Exception $e) {
            Log::error('Get users failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear all attendance logs from device
     * Device ki memory full hone par use karo
     */
    public function clearDeviceLogs()
    {
        if (!$this->connect()) {
            return false;
        }

        try {
            $this->device->clearAttendance();
            $this->device->disconnect();
            Log::info('Device logs cleared');
            return true;
        } catch (\Exception $e) {
            Log::error('Clear logs failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test device connection
     */
    public function testConnection()
    {
        if (!$this->connect()) {
            return [
                'success' => false,
                'message' => 'Connection failed'
            ];
        }

        try {
            $info = [
                'serial_number' => $this->device->serialNumber(),
                'device_name' => $this->device->deviceName(),
                'firmware' => $this->device->version(),
                'platform' => $this->device->platform(),
                'users_count' => count($this->device->getUser()),
                'attendance_count' => count($this->device->getAttendance())
            ];

            $this->device->disconnect();

            return [
                'success' => true,
                'info' => $info
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Determine attendance status based on shift
     */
    protected function determineStatus($user, $checkInTime)
    {
        $shift = $user->shift;
        $shiftStart = Carbon::parse($shift->start_time);
        $graceEnd = $shiftStart->addMinutes($shift->grace_period);

        if ($checkInTime->greaterThan($graceEnd)) {
            return 'late';
        }

        return 'present';
    }
}