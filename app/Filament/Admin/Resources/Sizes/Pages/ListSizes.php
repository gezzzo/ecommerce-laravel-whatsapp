<?php

namespace App\Filament\Admin\Resources\Sizes\Pages;

use App\Filament\Admin\Resources\Sizes\SizeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSizes extends ListRecords
{
    protected static string $resource = SizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
