<?php

namespace App\Filament\Developer\DeliveryProviders\Pages;

use App\Filament\Developer\DeliveryProviders\DeliveryProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryProviders extends ListRecords
{
    protected static string $resource = DeliveryProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
