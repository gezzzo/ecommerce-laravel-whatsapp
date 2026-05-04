<?php

namespace App\Filament\Admin\Resources\WhatsappCampaigns\Pages;

use App\Filament\Admin\Resources\WhatsappCampaigns\WhatsappCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappCampaign extends EditRecord
{
    protected static string $resource = WhatsappCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Sync total_contacts after repeater saves
        $this->record->update([
            'total_contacts' => $this->record->contacts()->count(),
        ]);
    }
}
