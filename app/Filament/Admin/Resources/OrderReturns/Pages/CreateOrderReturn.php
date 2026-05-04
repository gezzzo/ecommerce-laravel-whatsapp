<?php

namespace App\Filament\Admin\Resources\OrderReturns\Pages;

use App\Filament\Admin\Resources\OrderReturns\OrderReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderReturn extends CreateRecord
{
    protected static string $resource = OrderReturnResource::class;
}
