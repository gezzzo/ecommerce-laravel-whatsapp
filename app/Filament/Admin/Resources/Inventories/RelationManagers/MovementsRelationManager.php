<?php

namespace App\Filament\Admin\Resources\Inventories\RelationManagers;

use App\Enums\InventoryMovementType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Stock Movements';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (InventoryMovementType $state): string => $state->color())
                    ->icon(fn (InventoryMovementType $state): string => $state->icon())
                    ->formatStateUsing(fn (InventoryMovementType $state): string => $state->label()),
                TextColumn::make('quantity')
                    ->numeric()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "+{$state}" : (string) $state),
                TextColumn::make('notes')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('creator.name')
                    ->label(__('Created By'))
                    ->toggleable(),
                TextColumn::make('reference_type')
                    ->label(__('Reference'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'App\\Models\\Order' => 'Order',
                        'App\\Models\\OrderReturn' => 'Return',
                        null => '-',
                        default => class_basename($state),
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(
                        collect(InventoryMovementType::cases())
                            ->mapWithKeys(fn (InventoryMovementType $type) => [$type->value => $type->label()])
                            ->all()
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
