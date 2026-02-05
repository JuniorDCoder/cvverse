<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'thumbnail',
        'is_active',
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
    ];

    protected $casts = [
        'layout' => 'array',
        'styles' => 'array',
        'sections' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'price' => 'decimal:2',
        'downloads_count' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Get template categories.
     */
    public static function categories(): array
    {
        return [
            'professional' => 'Professional',
            'creative' => 'Creative',
            'minimal' => 'Minimal',
            'modern' => 'Modern',
            'executive' => 'Executive',
            'academic' => 'Academic',
            'technical' => 'Technical',
        ];
    }

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for free templates.
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope for premium templates.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Increment downloads count.
     */
    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get the default styles for a new template.
     */
    public static function defaultStyles(): array
    {
        return [
            'primaryColor' => '#2563eb',
            'secondaryColor' => '#64748b',
            'backgroundColor' => '#ffffff',
            'textColor' => '#1f2937',
            'headingColor' => '#111827',
            'accentColor' => '#3b82f6',
            'fontFamily' => 'Inter, sans-serif',
            'headingFont' => 'Inter, sans-serif',
            'fontSize' => '14px',
            'lineHeight' => '1.6',
            'spacing' => 'normal',
            'borderRadius' => '4px',
        ];
    }

    /**
     * Get the default sections for a new template.
     */
    public static function defaultSections(): array
    {
        return [
            ['id' => 'header', 'name' => 'Header', 'enabled' => true, 'order' => 0],
            ['id' => 'summary', 'name' => 'Professional Summary', 'enabled' => true, 'order' => 1],
            ['id' => 'experience', 'name' => 'Work Experience', 'enabled' => true, 'order' => 2],
            ['id' => 'education', 'name' => 'Education', 'enabled' => true, 'order' => 3],
            ['id' => 'skills', 'name' => 'Skills', 'enabled' => true, 'order' => 4],
            ['id' => 'projects', 'name' => 'Projects', 'enabled' => false, 'order' => 5],
            ['id' => 'certifications', 'name' => 'Certifications', 'enabled' => false, 'order' => 6],
            ['id' => 'languages', 'name' => 'Languages', 'enabled' => false, 'order' => 7],
        ];
    }

    /**
     * Get the default layout for a new template.
     */
    public static function defaultLayout(): array
    {
        return [
            'columns' => 1,
            'headerStyle' => 'centered',
            'sidebarPosition' => 'none',
            'sectionStyle' => 'simple',
        ];
    }
}
