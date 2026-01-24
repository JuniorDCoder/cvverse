<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    /** @use HasFactory<\Database\Factories\JobApplicationFactory> */
    use HasFactory;

    public const STATUS_SAVED = 'saved';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_INTERVIEWING = 'interviewing';
    public const STATUS_OFFERED = 'offered';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUSES = [
        self::STATUS_SAVED,
        self::STATUS_APPLIED,
        self::STATUS_INTERVIEWING,
        self::STATUS_OFFERED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN,
    ];

    protected $fillable = [
        'user_id',
        'company_id',
        'cv_id',
        'cover_letter_id',
        'title',
        'description',
        'requirements',
        'skills',
        'salary_range',
        'location',
        'work_type',
        'experience_level',
        'source_url',
        'status',
        'applied_at',
        'deadline',
        'notes',
        'ai_analysis',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'skills' => 'array',
            'ai_analysis' => 'array',
            'applied_at' => 'date',
            'deadline' => 'date',
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
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Cv, $this>
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }

    /**
     * @return BelongsTo<CoverLetter, $this>
     */
    public function coverLetter(): BelongsTo
    {
        return $this->belongsTo(CoverLetter::class);
    }

    /**
     * @return HasMany<CvVersion, $this>
     */
    public function cvVersions(): HasMany
    {
        return $this->hasMany(CvVersion::class);
    }
}
