<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the scan_type enum to include 'qr'
        DB::statement("ALTER TABLE tbl_attendances MODIFY COLUMN scan_type ENUM('biometric', 'manual', 'adjustment', 'qr') DEFAULT 'biometric'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE tbl_attendances MODIFY COLUMN scan_type ENUM('biometric', 'manual', 'adjustment') DEFAULT 'biometric'");
    }
};
