<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'form_id',
        'submission_number',
        'name',
        'email',
        'phone',
        'data',
        'pricing_tier_id',
        'selected_upsells',
        'tier_amount',
        'upsells_amount',
        'affiliate_amount',
        'total_amount',
        'payment_status',
        'payment_method',
        'paid_at',
        'affiliate_reward_id',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'selected_upsells' => 'array',
        'tier_amount' => 'decimal:2',
        'upsells_amount' => 'decimal:2',
        'affiliate_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($submission) {
            if (empty($submission->submission_number)) {
                $submission->submission_number = self::generateSubmissionNumber();
            }
        });
    }

    public static function generateSubmissionNumber(): string
    {
        $year = date('Y');
        $lastSubmission = self::whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        $nextNumber = $lastSubmission
            ? intval(substr($lastSubmission->submission_number, -5)) + 1
            : 1;

        return 'SUB-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function affiliateReward(): BelongsTo
    {
        return $this->belongsTo(AffiliateReward::class);
    }

    public function announcement(): HasOne
    {
        return $this->hasOne(Announcement::class);
    }

    // Scope by payment status
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    // Scope for paid submissions
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Scope for submissions with affiliate
    public function scopeWithAffiliate($query)
    {
        return $query->whereNotNull('affiliate_code');
    }
}
