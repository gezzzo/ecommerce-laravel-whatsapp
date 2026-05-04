<?php

namespace App\Enums;

enum WhatsappSessionStatus: string
{
    case Disconnected = 'disconnected';
    case Connected = 'connected';

    public function label(): string
    {
        return match ($this) {
            self::Disconnected => __('Disconnected'),
            self::Connected => __('Connected'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Disconnected => 'danger',
            self::Connected => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Disconnected => 'heroicon-o-x-circle',
            self::Connected => 'heroicon-o-check-circle',
        };
    }
}
