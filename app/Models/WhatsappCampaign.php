<?php

namespace App\Models;

use App\Enums\WhatsappCampaignStatus;
use App\Enums\WhatsappMediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappCampaign extends Model
{
    protected $fillable = [
        'whatsapp_session_id',
        'name',
        'message',
        'media_url',
        'media_type',
        'media_caption',
        'status',
        'scheduled_at',
        'delay_seconds',
        'total_contacts',
        'sent_count',
        'failed_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WhatsappCampaignStatus::class,
            'media_type' => WhatsappMediaType::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WhatsappSession::class, 'whatsapp_session_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(WhatsappCampaignContact::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WhatsappCampaignLog::class);
    }

    public function pendingContacts(): HasMany
    {
        return $this->contacts()->where('status', 'pending');
    }

    /** Percentage of contacts processed (sent + failed). */
    public function progressPercentage(): int
    {
        if ($this->total_contacts === 0) {
            return 0;
        }

        return (int) round(
            (($this->sent_count + $this->failed_count) / $this->total_contacts) * 100
        );
    }

    public function hasMedia(): bool
    {
        return $this->media_type !== WhatsappMediaType::None && filled($this->media_url);
    }
}
