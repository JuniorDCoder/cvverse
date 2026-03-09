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
        Schema::table('help_conversations', function (Blueprint $table) {
            $table->timestamp('user_typing_at')->nullable();
            $table->timestamp('admin_typing_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('help_conversations', function (Blueprint $table) {
            $table->dropColumn(['user_typing_at', 'admin_typing_at']);
        });
    }
};
