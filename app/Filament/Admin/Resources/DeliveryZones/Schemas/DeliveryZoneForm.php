<?php

namespace App\Filament\Admin\Resources\DeliveryZones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('delivery_company_id')
                    ->relationship('company', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('external_city_id')
                    ->maxLength(255),
            ]);
    }
}
