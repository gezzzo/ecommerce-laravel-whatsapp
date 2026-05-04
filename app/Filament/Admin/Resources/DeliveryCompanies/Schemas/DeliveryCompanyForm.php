<?php

namespace App\Filament\Admin\Resources\DeliveryCompanies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryCompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('delivery_provider_id')
                    ->relationship('provider', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('api_token')
                    ->maxLength(255),
                TextInput::make('client_key')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
