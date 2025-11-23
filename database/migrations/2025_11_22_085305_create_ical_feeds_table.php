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
        Schema::create('ical_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->enum('direction', ['import', 'export']);
            $table->string('url')->nullable(); // For import
            $table->string('token')->nullable()->unique(); // For export
            $table->boolean('active')->default(true);
            $table->dateTime('last_synced_at')->nullable();
            $table->json('sync_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ical_feeds');
    }
};
