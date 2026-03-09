<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HelpConversation extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'subject',
        'status',
        'last_admin_activity_at',
        'ai_timeout_minutes',
        'user_typing_at',
        'admin_typing_at',
    ];

    protected function casts(): array
    {
        return [
            'last_admin_activity_at' => 'datetime',
            'user_typing_at' => 'datetime',
            'admin_typing_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HelpMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(HelpMessage::class)->latestOfMany();
    }

    public function unreadMessagesForAdmin(): HasMany
    {
        return $this->hasMany(HelpMessage::class)
            ->where('sender_type', 'user')
            ->whereNull('read_at');
    }

    public function unreadMessagesForUser(): HasMany
    {
        return $this->hasMany(HelpMessage::class)
            ->whereIn('sender_type', ['admin', 'ai'])
            ->whereNull('read_at');
    }

    public function isAiTimedOut(): bool
    {
        if ($this->status === 'ai_active') {
            return true;
        }

        if (! $this->last_admin_activity_at) {
            return $this->created_at->diffInMinutes(now()) >= $this->ai_timeout_minutes;
        }

        return $this->last_admin_activity_at->diffInMinutes(now()) >= $this->ai_timeout_minutes;
    }
}
