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
        Schema::table('cv_templates', function (Blueprint $table) {
            // Template builder fields
            $table->json('layout')->nullable()->after('description'); // Stores sections, their order, and positions
            $table->json('styles')->nullable()->after('layout'); // Colors, fonts, spacing
            $table->json('sections')->nullable()->after('styles'); // Available sections config
            $table->text('html_content')->nullable()->after('sections'); // Final rendered HTML with placeholders
            $table->text('css_content')->nullable()->after('html_content'); // Custom CSS for the template

            // Pricing & access
            $table->string('category')->default('professional')->after('css_content');
            $table->boolean('is_premium')->default(false)->after('category');
            $table->decimal('price', 10, 2)->nullable()->after('is_premium');
            $table->string('currency')->default('USD')->after('price');

            // Stats
            $table->unsignedBigInteger('downloads_count')->default(0)->after('currency');
            $table->unsignedBigInteger('views_count')->default(0)->after('downloads_count');

            // Preview
            $table->string('thumbnail')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cv_templates', function (Blueprint $table) {
            $table->dropColumn([
                'layout',
                'styles',
                'sections',
                'html_content',
                'css_content',
                'category',
                'is_premium',
                'price',
                'currency',
                'downloads_count',
                'views_count',
                'thumbnail',
            ]);
        });
    }
};
