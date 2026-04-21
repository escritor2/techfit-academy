<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 5, 2); // kg
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('body_fat', 4, 1)->nullable(); // %
            $table->decimal('chest', 5, 2)->nullable();
            $table->decimal('waist', 5, 2)->nullable();
            $table->decimal('biceps', 5, 2)->nullable();
            $table->decimal('thigh', 5, 2)->nullable();
            $table->date('measured_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_metrics');
    }
};
