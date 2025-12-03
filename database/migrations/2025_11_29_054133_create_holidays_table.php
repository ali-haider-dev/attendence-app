<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Eid, Christmas, etc
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('type', ['public', 'optional'])->default('public');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_holidays');
    }
};
