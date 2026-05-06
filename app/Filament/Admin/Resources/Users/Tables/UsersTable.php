<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('Phone number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_guest')
                    ->label(__('Customer type'))
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Guest') : __('Registered'))
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('Email address'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label(__('Email Verified At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label(__('Orders'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
