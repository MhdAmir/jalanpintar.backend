<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Field extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'section_id',
        'label',
        'name',
        'type',
        'placeholder',
        'help_text',
        'is_required',
        'order',
        'options',
        'validation_rules',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
        'options' => 'array',
        'validation_rules' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    // Scope for required fields
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}
