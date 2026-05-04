<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappCampaignContact extends Model
{
    protected $fillable = [
        'whatsapp_campaign_id',
        'phone',
        'name',
        'status',
        'error_message',
        'sent_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaign::class, 'whatsapp_campaign_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WhatsappCampaignLog::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /** Normalize the phone to international format (digits only). */
    public function normalizedPhone(): string
    {
        return preg_replace('/\D/', '', $this->phone);
    }
}
