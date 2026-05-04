<?php

namespace App\Filament\Admin\Resources\Inventories\Pages;

use App\Filament\Admin\Resources\Inventories\InventoryResource;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;
}
