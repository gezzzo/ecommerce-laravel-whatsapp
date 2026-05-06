<?php

namespace App\Filament\Admin\Resources\DeliveryZones\Schemas;

use App\Models\DeliveryCompany;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('delivery_company_id')
                    ->label(__('Delivery Company'))
                    ->relationship(
                        'company',
                        'id',
                        fn (Builder $query): Builder => $query->with('provider'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (DeliveryCompany $record): string => $record->provider?->name
                            ?? __('Delivery Company').' #'.$record->getKey(),
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('city')
                    ->label(__('City'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('delivery_fee')
                    ->label(__('Delivery Fee'))
                    ->required()
                    ->numeric()
                    ->prefix(__('MAD')),
                TextInput::make('external_city_id')
                    ->label(__('External ID'))
                    ->maxLength(255),
            ]);
    }
}
