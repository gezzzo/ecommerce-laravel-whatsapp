<?php

namespace App\Filament\Admin\Resources\WhatsappSessions\Pages;

use App\Filament\Admin\Resources\WhatsappSessions\WhatsappSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappSession extends EditRecord
{
    protected static string $resource = WhatsappSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
