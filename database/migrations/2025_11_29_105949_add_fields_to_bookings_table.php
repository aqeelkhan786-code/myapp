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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('job')->nullable()->after('guest_last_name');
            $table->enum('language', ['Deutsch', 'Englisch'])->nullable()->after('job');
            $table->enum('communication_preference', ['Mail', 'Whatsapp'])->nullable()->after('language');
            $table->string('renter_address')->nullable()->after('phone');
            $table->string('renter_postal_code')->nullable()->after('renter_address');
            $table->string('renter_city')->nullable()->after('renter_postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['job', 'language', 'communication_preference', 'renter_address', 'renter_postal_code', 'renter_city']);
        });
    }
};
