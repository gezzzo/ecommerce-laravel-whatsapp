<?php

namespace App\Filament\Admin\Resources\WhatsappInbox\Pages;

use App\Filament\Admin\Resources\WhatsappInbox\WhatsappInboxResource;
use App\Models\WhatsappInboxMessage;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappInbox extends ListRecords
{
    protected static string $resource = WhatsappInboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label(__('Mark All as Read'))
                ->icon('heroicon-o-check-badge')
                ->color('gray')
                ->requiresConfirmation()
                ->action(fn () => WhatsappInboxMessage::unread()->update(['is_read' => true])),
        ];
    }
}
