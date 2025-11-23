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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('doc_type', ['rental_agreement', 'landlord_confirmation', 'rent_arrears']);
            $table->enum('locale', ['en', 'de'])->default('en');
            $table->string('storage_path');
            $table->integer('version')->default(1);
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->json('signature_data')->nullable();
            $table->dateTime('sent_to_customer_at')->nullable();
            $table->dateTime('sent_to_owner_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
