<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappInboxMessage extends Model
{
    protected $fillable = [
        'whatsapp_session_id',
        'message_id',
        'from',
        'push_name',
        'text',
        'message_type',
        'is_group',
        'is_read',
        'raw_payload',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
            'is_read' => 'boolean',
            'raw_payload' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WhatsappSession::class, 'whatsapp_session_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeFromPhone(Builder $query, string $phone): Builder
    {
        return $query->where('from', preg_replace('/\D/', '', $phone));
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
