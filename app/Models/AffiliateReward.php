<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateReward extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'form_id',
        'user_id',
        'affiliate_code',
        'commission_type',
        'commission_value',
        'total_earned',
        'total_referrals',
        'is_active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_referrals' => 'integer',
        'is_active' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    // Scope for active affiliates
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
