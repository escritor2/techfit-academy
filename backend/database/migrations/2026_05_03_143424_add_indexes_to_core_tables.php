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
        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'role']);
        });

        Schema::table('workouts', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });

        Schema::table('body_metrics', function (Blueprint $table) {
            $table->index(['user_id', 'measured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'role']);
        });

        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['expires_at']);
        });

        Schema::table('body_metrics', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'measured_at']);
        });
    }
};
