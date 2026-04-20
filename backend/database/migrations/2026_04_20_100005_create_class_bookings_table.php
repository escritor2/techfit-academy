<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('gym_class_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['booked', 'cancelled'])->default('booked');
            $table->timestamps();

            $table->unique(['user_id', 'gym_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_bookings');
    }
};
