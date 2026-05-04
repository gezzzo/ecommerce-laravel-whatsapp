<?php

namespace App\Enums;

enum WhatsappMediaType: string
{
    case None = 'none';
    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
    case Audio = 'audio';

    public function label(): string
    {
        return match ($this) {
            self::None => __('Text Only'),
            self::Image => __('Image'),
            self::Video => __('Video'),
            self::Document => __('Document'),
            self::Audio => __('Audio'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::None => 'heroicon-o-chat-bubble-left',
            self::Image => 'heroicon-o-photo',
            self::Video => 'heroicon-o-video-camera',
            self::Document => 'heroicon-o-document',
            self::Audio => 'heroicon-o-musical-note',
        };
    }
}
