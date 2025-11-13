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

    // Check if form has affiliate field (now automatic based on enable_affiliate)
    public function hasAffiliateField(): bool
    {
        return $this->enable_affiliate === true;
    }

    // Get affiliate field from form (auto-generated)
    public function getAffiliateField($sectionId = null)
    {
        return new Field([
            'id' => 'virtual-affiliate-field',
            'section_id' => $sectionId,
            'label' => 'Kode Referral (Opsional)',
            'name' => 'affiliate_code',
            'type' => 'affiliate',
            'placeholder' => 'Masukkan kode referral jika ada',
            'help_text' => 'Dapatkan diskon atau benefit dengan memasukkan kode referral dari teman Anda',
            'is_required' => false,
            'options' => null,
            'validation_rules' => null,
            'order' => 1,
        ]);
    }

    // Get payment tier field (auto-generated)
    public function getPaymentTierField($sectionId = null)
    {
        return new Field([
            'id' => 'virtual-payment-tier-field',
            'section_id' => $sectionId,
            'label' => 'Pilih Paket',
            'name' => 'pricing_tier_id',
            'type' => 'select',
            'placeholder' => 'Pilih paket yang sesuai',
            'help_text' => 'Pilih paket pembayaran sesuai kebutuhan Anda',
            'is_required' => true,
            'options' => null, // Will be filled dynamically from pricing_tiers
            'validation_rules' => null,
            'order' => 0,
        ]);
    }

    // Override sections relation to auto-inject payment/affiliate section
    public function getSectionsWithAffiliateAttribute()
    {
        $sections = $this->sections()->with('fields')->orderBy('order')->get();

        $hasPayment = $this->enable_payment;
        $hasAffiliate = $this->enable_affiliate;

        // If either is enabled, create virtual section
        if ($hasPayment || $hasAffiliate) {
            // Determine section title and description
            if ($hasPayment && $hasAffiliate) {
                $sectionTitle = 'Pembayaran & Referral';
                $sectionDescription = 'Pilih paket pembayaran dan masukkan kode referral jika ada';
            } elseif ($hasPayment) {
                $sectionTitle = 'Informasi Pembayaran';
                $sectionDescription = 'Pilih paket pembayaran yang sesuai dengan kebutuhan Anda';
            } else {
                $sectionTitle = 'Kode Referral';
                $sectionDescription = 'Masukkan kode referral untuk mendapatkan benefit';
            }

            // Create virtual section
            $virtualSection = new Section([
                'id' => 'virtual-payment-affiliate-section',
                'form_id' => $this->id,
                'title' => $sectionTitle,
                'description' => $sectionDescription,
                'order' => 9999, // Always last
            ]);
            $virtualSection->exists = true;
            $virtualSection->setRelation('fields', collect());

            // Add payment tier field if enabled
            if ($hasPayment) {
                $paymentField = $this->getPaymentTierField($virtualSection->id);
                $paymentField->exists = true;

                // Add pricing tiers as options
                $pricingTiers = $this->pricingTiers()->where('is_active', true)->orderBy('order')->get();
                $paymentField->options = $pricingTiers->map(function ($tier) {
                    return [
                        'value' => $tier->id,
                        'label' => $tier->name . ' - Rp ' . number_format($tier->price, 0, ',', '.'),
                        'price' => $tier->price,
                    ];
                })->toArray();

                $virtualSection->fields->push($paymentField);
            }

            // Add affiliate field if enabled
            if ($hasAffiliate) {
                $affiliateField = $this->getAffiliateField($virtualSection->id);
                $affiliateField->exists = true;
                $virtualSection->fields->push($affiliateField);
            }

            // Add virtual section to the end
            $sections->push($virtualSection);
        }

        return $sections;
    }
}
