<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upsell extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'form_id',
        'name',
        'description',
        'price',
        'currency',
        'image',
        'is_active',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    // Scope for active upsells
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
