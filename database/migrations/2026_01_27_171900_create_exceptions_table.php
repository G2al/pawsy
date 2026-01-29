<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('Data specifica eccezione');
            $table->boolean('is_closed')->default(true)->comment('true=chiuso, false=aperto con orari speciali');
            $table->time('morning_open')->nullable();
            $table->time('morning_close')->nullable();
            $table->time('afternoon_open')->nullable();
            $table->time('afternoon_close')->nullable();
            $table->string('reason')->nullable()->comment('Motivo: Natale, Ferie, Evento, ecc.');
            $table->timestamps();
            
            $table->index('date');
            $table->index('is_closed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exceptions');
    }
};