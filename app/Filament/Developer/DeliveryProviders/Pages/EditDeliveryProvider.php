<?php

namespace App\Filament\Developer\DeliveryProviders\Pages;

use App\Filament\Developer\DeliveryProviders\DeliveryProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryProvider extends EditRecord
{
    protected static string $resource = DeliveryProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
