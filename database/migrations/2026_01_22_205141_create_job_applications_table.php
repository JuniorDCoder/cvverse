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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cv_id')->nullable()->constrained('cvs')->nullOnDelete();
            $table->foreignId('cover_letter_id')->nullable()->constrained('cover_letters')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('requirements')->nullable();
            $table->json('skills')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('location')->nullable();
            $table->string('work_type')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('source_url')->nullable();
            $table->enum('status', ['saved', 'applied', 'interviewing', 'offered', 'rejected', 'withdrawn'])->default('saved');
            $table->date('applied_at')->nullable();
            $table->date('deadline')->nullable();
            $table->text('notes')->nullable();
            $table->json('ai_analysis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
