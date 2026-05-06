<?php

namespace App\Filament\Admin\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('Code'))
                    ->required(),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'percent' => __('Percent'),
                        'fixed' => __('Fixed'),
                    ])
                    ->default('fixed')
                    ->required(),
                TextInput::make('value')
                    ->label(__('Value'))
                    ->required()
                    ->numeric(),
                TextInput::make('max_uses')
                    ->label(__('Max Uses'))
                    ->numeric()
                    ->default(null),
                TextInput::make('used_count')
                    ->label(__('Used Count'))
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('expires_at')
                    ->label(__('Expires At')),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->required(),
            ]);
    }
}
