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
            // Role: admin or user
            $table->string('role')->default('user')->after('email');

            // Profile fields for onboarding
            $table->string('avatar')->nullable()->after('role');
            $table->string('job_title')->nullable()->after('avatar');
            $table->string('industry')->nullable()->after('job_title');
            $table->string('experience_level')->nullable()->after('industry');
            $table->json('interests')->nullable()->after('experience_level');
            $table->string('phone')->nullable()->after('interests');
            $table->string('location')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('location');

            // Onboarding status
            $table->boolean('onboarding_completed')->default(false)->after('bio');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'avatar',
                'job_title',
                'industry',
                'experience_level',
                'interests',
                'phone',
                'location',
                'bio',
                'onboarding_completed',
                'onboarding_completed_at',
            ]);
        });
    }
};
