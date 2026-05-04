<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappCampaignLog extends Model
{
    protected $fillable = [
        'whatsapp_campaign_id',
        'whatsapp_campaign_contact_id',
        'phone',
        'event',
        'message',
        'payload',
        'happened_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'happened_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaign::class, 'whatsapp_campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaignContact::class, 'whatsapp_campaign_contact_id');
    }
}
