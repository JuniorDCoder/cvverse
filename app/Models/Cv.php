<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cv extends Model
{
    /** @use HasFactory<\Database\Factories\CvFactory> */
    use HasFactory;

    protected $table = 'cvs';

    public const TEMPLATES = [
        'modern' => 'Modern',
        'classic' => 'Classic',
        'minimal' => 'Minimal',
        'creative' => 'Creative',
        'executive' => 'Executive',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'template',
        'is_primary',
        'personal_info',
        'experience',
        'education',
        'skills',
        'projects',
        'certifications',
        'languages',
        'summary',
        'ai_suggestions',
        'file_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'personal_info' => 'array',
            'experience' => 'array',
            'education' => 'array',
            'skills' => 'array',
            'projects' => 'array',
            'certifications' => 'array',
            'languages' => 'array',
            'ai_suggestions' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CvVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(CvVersion::class)->orderByDesc('version_number');
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function createVersion(?string $changeSummary = null, ?int $jobApplicationId = null): CvVersion
    {
        $latestVersion = $this->versions()->max('version_number') ?? 0;

        return $this->versions()->create([
            'version_number' => $latestVersion + 1,
            'content' => [
                'personal_info' => $this->personal_info,
                'experience' => $this->experience,
                'education' => $this->education,
                'skills' => $this->skills,
                'projects' => $this->projects,
                'certifications' => $this->certifications,
                'languages' => $this->languages,
                'summary' => $this->summary,
            ],
            'change_summary' => $changeSummary,
            'job_application_id' => $jobApplicationId,
        ]);
    }
}
