<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_classes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('day_of_week')->after('schedule');
            $table->time('start_time')->after('day_of_week');
            $table->time('end_time')->after('start_time');
            $table->boolean('is_active')->default(true)->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('gym_classes', function (Blueprint $table) {
            $table->dropColumn(['description', 'day_of_week', 'start_time', 'end_time', 'is_active']);
        });
    }
};
