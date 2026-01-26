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
        Schema::create('cv_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('cv_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->enum('permission', ['view', 'review', 'edit'])->default('view');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_shares');
    }
};
