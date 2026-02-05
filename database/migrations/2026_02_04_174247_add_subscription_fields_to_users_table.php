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
            $table->foreignId('pricing_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->string('subscription_status')->default('free'); // free, active, expired, cancelled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn(['pricing_plan_id', 'subscription_ends_at', 'subscription_status']);
        });
    }
};
