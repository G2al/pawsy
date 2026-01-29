<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->integer('day_of_week')->unique()->comment('0=Domenica, 1=Lunedì, 2=Martedì, 3=Mercoledì, 4=Giovedì, 5=Venerdì, 6=Sabato');
            $table->time('morning_open')->nullable();
            $table->time('morning_close')->nullable();
            $table->time('afternoon_open')->nullable();
            $table->time('afternoon_close')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            
            $table->index('day_of_week');
            $table->index('is_closed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};