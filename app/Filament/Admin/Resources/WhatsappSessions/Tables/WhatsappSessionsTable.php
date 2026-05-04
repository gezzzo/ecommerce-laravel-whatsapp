<?php

namespace App\Filament\Admin\Resources\WhatsappSessions\Tables;

use App\Enums\WhatsappSessionStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WhatsappSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('instance_id')
                    ->label(__('Instance ID'))
                    ->searchable()
                    ->copyable()
                    ->limit(30)
                    ->fontFamily('mono')
                    ->tooltip(fn ($record) => $record->instance_id),

                TextColumn::make('phone_number')
                    ->label(__('Phone'))
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (WhatsappSessionStatus $state) => $state->color())
                    ->icon(fn (WhatsappSessionStatus $state) => $state->icon())
                    ->formatStateUsing(fn (WhatsappSessionStatus $state) => $state->label()),

                TextColumn::make('connected_at')
                    ->label(__('Connected At'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('campaigns_count')
                    ->label(__('Campaigns'))
                    ->counts('campaigns')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
