<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label(__('Thumbnail'))
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('skuCode.sku_code')
                    ->label(__('SKU'))
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->copyable(),
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->label(__('Selling Price'))
                    ->money('MAD')
                    ->sortable(),
                TextColumn::make('price_before_discount')
                    ->label(__('Price Before Discount'))
                    ->money('MAD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_variants')
                    ->label(__('Has Variants'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->boolean(),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label(__('Variants'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('inventory.quantity')
                    ->label(__('Stock'))
                    ->default(0)
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        (int) $state <= 0 => 'danger',
                        (int) $state <= 10 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category')
                    ->label(__('Category'))
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
                TernaryFilter::make('is_featured')
                    ->label(__('Featured')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
