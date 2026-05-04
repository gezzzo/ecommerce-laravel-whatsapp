<?php

namespace App\Models;

use App\Enums\WhatsappSessionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappSession extends Model
{
    protected $fillable = [
        'name',
        'instance_id',
        'api_token',
        'phone_number',
        'status',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WhatsappSessionStatus::class,
            'connected_at' => 'datetime',
            'api_token' => 'encrypted',
        ];
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(WhatsappCampaign::class);
    }

    public function isConnected(): bool
    {
        return $this->status === WhatsappSessionStatus::Connected;
    }
}
