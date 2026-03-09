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
        Schema::create('help_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->enum('status', ['open', 'ai_active', 'closed'])->default('open');
            $table->timestamp('last_admin_activity_at')->nullable();
            $table->integer('ai_timeout_minutes')->default(5);
            $table->timestamps();

            $table->index(['status', 'updated_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_conversations');
    }
};
