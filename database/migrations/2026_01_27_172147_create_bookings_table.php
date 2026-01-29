<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->date('booking_date');
            $table->time('time_slot');
            $table->integer('duration')->comment('Snapshot durata servizio al momento prenotazione');
            $table->decimal('price', 8, 2)->comment('Snapshot prezzo servizio al momento prenotazione');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->text('notes')->nullable()->comment('Note cliente sulla prenotazione');
            $table->text('admin_notes')->nullable()->comment('Note private admin');
            $table->timestamps();
            
            $table->unique(['booking_date', 'time_slot'], 'unique_booking_slot');
            $table->index('user_id');
            $table->index('pet_id');
            $table->index('service_id');
            $table->index('booking_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};