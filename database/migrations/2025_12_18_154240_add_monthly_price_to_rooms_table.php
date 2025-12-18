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
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('monthly_price', 10, 2)->nullable()->after('base_price');
        });
        
        // Set default monthly_price to 700 for existing rooms
        \DB::table('rooms')->update(['monthly_price' => 700.00]);
        
        // Set default capacity to 1 for existing rooms
        \DB::table('rooms')->update(['capacity' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('monthly_price');
        });
    }
};
