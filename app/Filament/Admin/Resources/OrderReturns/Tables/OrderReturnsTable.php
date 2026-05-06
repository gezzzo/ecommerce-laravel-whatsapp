<?php

namespace App\Filament\Admin\Resources\OrderReturns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label(__('Order'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orderItem.name')
                    ->label(__('Item'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('refund_amount')
                    ->label(__('Refund Amount'))
                    ->money('MAD')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        'completed' => __('Completed'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($record): string => match ($record->status) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('inventory_restored')
                    ->label(__('Inventory Restored'))
                    ->boolean(),
                TextColumn::make('processed_at')
                    ->label(__('Processed At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        'completed' => __('Completed'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
