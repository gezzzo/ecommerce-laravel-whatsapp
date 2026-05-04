<?php

namespace App\Filament\Admin\Resources\DeliveryCompanies\Pages;

use App\Filament\Admin\Resources\DeliveryCompanies\DeliveryCompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryCompany extends EditRecord
{
    protected static string $resource = DeliveryCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
