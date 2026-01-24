<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverLetter extends Model
{
    /** @use HasFactory<\Database\Factories\CoverLetterFactory> */
    use HasFactory;

    public const TONES = [
        'professional' => 'Professional',
        'enthusiastic' => 'Enthusiastic',
        'confident' => 'Confident',
        'conversational' => 'Conversational',
        'formal' => 'Formal',
    ];

    protected $fillable = [
        'user_id',
        'job_application_id',
        'name',
        'content',
        'tone',
        'ai_improvements',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ai_improvements' => 'array',
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
     * @return BelongsTo<JobApplication, $this>
     */
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
