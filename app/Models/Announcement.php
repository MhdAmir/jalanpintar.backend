<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'form_id',
        'submission_id',
        'identifier',
        'name',
        'status',
        'result_data',
        'notes',
        'announced_at',
    ];

    protected $casts = [
        'result_data' => 'array',
        'announced_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    // Scope by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for accepted announcements
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    // Scope for rejected announcements
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Scope for pending announcements
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
