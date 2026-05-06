<?php

namespace App\Filament\Admin\Resources\Sizes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('type')
                    ->label(__('Type'))
                    ->maxLength(255),
            ]);
    }
}
