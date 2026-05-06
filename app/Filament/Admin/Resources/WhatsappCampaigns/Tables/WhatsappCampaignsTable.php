<?php

namespace App\Filament\Admin\Resources\WhatsappCampaigns\Tables;

use App\Enums\WhatsappCampaignStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsappCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Campaign'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('session.name')
                    ->label(__('Session'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (WhatsappCampaignStatus $state) => $state->color())
                    ->icon(fn (WhatsappCampaignStatus $state) => $state->icon())
                    ->formatStateUsing(fn (WhatsappCampaignStatus $state) => $state->label())
                    ->sortable(),

                TextColumn::make('total_contacts')
                    ->label(__('Total'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sent_count')
                    ->label(__('Sent'))
                    ->numeric()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('failed_count')
                    ->label(__('Failed'))
                    ->numeric()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label(__('Scheduled'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('Manual')),

                TextColumn::make('completed_at')
                    ->label(__('Completed'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(collect(WhatsappCampaignStatus::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    )),

                SelectFilter::make('whatsapp_session_id')
                    ->label(__('Session'))
                    ->relationship('session', 'name'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
