<?php

namespace App\Enums;

enum WhatsappCampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Paused = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Scheduled => __('Scheduled'),
            self::Running => __('Running'),
            self::Completed => __('Completed'),
            self::Failed => __('Failed'),
            self::Paused => __('Paused'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'info',
            self::Running => 'warning',
            self::Completed => 'success',
            self::Failed => 'danger',
            self::Paused => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Scheduled => 'heroicon-o-clock',
            self::Running => 'heroicon-o-play',
            self::Completed => 'heroicon-o-check-circle',
            self::Failed => 'heroicon-o-x-circle',
            self::Paused => 'heroicon-o-pause',
        };
    }
}
