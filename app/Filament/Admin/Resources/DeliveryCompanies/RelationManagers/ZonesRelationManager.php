<?php

namespace App\Filament\Admin\Resources\DeliveryCompanies\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ZonesRelationManager extends RelationManager
{
    protected static string $relationship = 'zones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('city')
            ->columns([
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_fee')
                    ->money('MAD')
                    ->sortable(),
                TextColumn::make('external_city_id')
                    ->label(__('External ID')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
