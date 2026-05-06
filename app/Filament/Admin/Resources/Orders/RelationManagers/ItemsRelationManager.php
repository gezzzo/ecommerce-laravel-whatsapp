<?php

namespace App\Filament\Admin\Resources\Orders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku_code')
                    ->label(__('SKU'))
                    ->maxLength(255),
                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->prefix(__('MAD')),
                TextInput::make('total')
                    ->label(__('Total'))
                    ->required()
                    ->numeric()
                    ->prefix(__('MAD')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label(__('Image'))
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('sku_code')
                    ->label(__('SKU')),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('MAD'),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('MAD'),
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
