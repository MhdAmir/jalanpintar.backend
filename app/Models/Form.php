<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'enable_payment',
        'enable_affiliate',
        'is_active',
        'published_at',
        'start_date',
        'end_date',
        'max_submissions',
        'settings',
    ];

    protected $casts = [
        'enable_payment' => 'boolean',
        'enable_affiliate' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(PricingTier::class)->orderBy('order');
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(Upsell::class)->orderBy('order');
    }

    public function affiliateRewards(): HasMany
    {
        return $this->hasMany(AffiliateReward::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    // Scope for active forms
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for forms with payment enabled
    public function scopeWithPayment($query)
    {
        return $query->where('enable_payment', true);
    }

    // Scope for forms with affiliate enabled
    public function scopeWithAffiliate($query)
    {
        return $query->where('enable_affiliate', true);
    }
}
