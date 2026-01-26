<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'cv_share_id',
        'user_id',
        'guest_name',
        'content',
        'section',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(CvShare::class, 'cv_share_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
