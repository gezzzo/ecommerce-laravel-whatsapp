<?php

namespace App\Filament\Developer\DeliveryProviders\Schemas;

use App\Support\ImageUploadHelper;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                TextInput::make('base_url')
                    ->url()
                    ->maxLength(255),
                ImageUploadHelper::make('logo')
                    ->directory('delivery-providers'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
