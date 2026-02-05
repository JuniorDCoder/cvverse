<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'pricing_plan_id',
        'subscription_ends_at',
        'subscription_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'interests' => 'array',
            'onboarding_completed' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the user's pricing plan.
     *
     * @return BelongsTo<PricingPlan, $this>
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Check if user has an active premium subscription.
     */
    public function hasPremiumAccess(): bool
    {
        // Admins always have premium access
        if ($this->isAdmin()) {
            return true;
        }

        // Check if user has active subscription that hasn't expired
        if ($this->subscription_status === 'active' && $this->pricing_plan_id) {
            if ($this->subscription_ends_at === null) {
                return true; // Lifetime or indefinite subscription
            }

            return $this->subscription_ends_at->isFuture();
        }

        return false;
    }

    /**
     * Check if user can access a specific template.
     */
    public function canAccessTemplate(CvTemplate $template): bool
    {
        // Free templates are accessible to everyone
        if (! $template->is_premium) {
            return true;
        }

        // Premium templates require premium access
        return $this->hasPremiumAccess();
    }

    /**
     * Check if the user has completed onboarding.
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed;
    }

    /**
     * Mark onboarding as complete.
     */
    public function completeOnboarding(): void
    {
        $this->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * @return HasMany<Cv, $this>
     */
    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }

    /**
     * @return HasMany<CoverLetter, $this>
     */
    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function primaryCv(): ?Cv
    {
        return $this->cvs()->where('is_primary', true)->first();
    }
}
