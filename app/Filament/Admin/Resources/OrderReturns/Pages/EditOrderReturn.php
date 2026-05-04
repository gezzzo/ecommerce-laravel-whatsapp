<?php

namespace App\Filament\Admin\Resources\OrderReturns\Pages;

use App\Filament\Admin\Resources\OrderReturns\OrderReturnResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderReturn extends EditRecord
{
    protected static string $resource = OrderReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
