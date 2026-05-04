<?php

namespace App\Filament\Admin\Resources\Colors\Pages;

use App\Filament\Admin\Resources\Colors\ColorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListColors extends ListRecords
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
