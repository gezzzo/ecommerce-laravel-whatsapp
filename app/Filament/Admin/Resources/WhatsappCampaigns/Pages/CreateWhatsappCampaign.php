<?php

namespace App\Filament\Admin\Resources\WhatsappCampaigns\Pages;

use App\Filament\Admin\Resources\WhatsappCampaigns\WhatsappCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappCampaign extends CreateRecord
{
    protected static string $resource = WhatsappCampaignResource::class;

    protected function afterCreate(): void
    {
        // Sync the total_contacts count after the repeater has saved contacts
        $this->record->update([
            'total_contacts' => $this->record->contacts()->count(),
        ]);
    }
}
