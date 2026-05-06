<?php

namespace App\Filament\Admin\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percent' => __('Percent'),
                        'fixed' => __('Fixed'),
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('value')
                    ->label(__('Value'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_uses')
                    ->label(__('Max Uses'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label(__('Used Count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('Expires At'))
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
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
