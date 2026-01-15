<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to alter the enum column to string
        // First, change the column type
        DB::statement("ALTER TABLE bookings MODIFY COLUMN communication_preference VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to enum - only if all values are valid enum values
        // First, update any 'Mail,Whatsapp' values to 'Mail' for rollback
        DB::statement("UPDATE bookings SET communication_preference = 'Mail' WHERE communication_preference = 'Mail,Whatsapp'");
        
        // Then change back to enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN communication_preference ENUM('Mail', 'Whatsapp') NULL");
    }
};
