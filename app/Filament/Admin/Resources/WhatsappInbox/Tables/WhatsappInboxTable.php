<?php

namespace App\Filament\Admin\Resources\WhatsappInbox\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsappInboxTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('success')
                    ->width('40px'),

                TextColumn::make('from')
                    ->label(__('From'))
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                TextColumn::make('push_name')
                    ->label(__('Name'))
                    ->searchable()
                    ->placeholder(__('Unknown')),

                TextColumn::make('text')
                    ->label(__('Message'))
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->text)
                    ->placeholder(__('— non-text message —')),

                TextColumn::make('message_type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'text' => 'gray',
                        'image' => 'success',
                        'video' => 'info',
                        'audio' => 'warning',
                        'document' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('session.name')
                    ->label(__('Session'))
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('received_at')
                    ->label(__('Received'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Filter::make('unread')
                    ->label(__('Unread Only'))
                    ->query(fn (Builder $query) => $query->where('is_read', false))
                    ->toggle(),

                SelectFilter::make('message_type')
                    ->options([
                        'text' => 'Text',
                        'image' => 'Image',
                        'audio' => 'Audio',
                        'video' => 'Video',
                        'document' => 'Document',
                        'unknown' => 'Other',
                    ]),

                SelectFilter::make('whatsapp_session_id')
                    ->label(__('Session'))
                    ->relationship('session', 'name'),
            ])
            ->actions([
                Action::make('mark_read')
                    ->label(__('Mark Read'))
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn ($record) => ! $record->is_read)
                    ->action(fn ($record) => $record->markAsRead()),

                Action::make('reply')
                    ->label(__('Reply'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->url(fn ($record) => route('filament.admin.resources.whatsapp-campaigns.create', [
                        'phone' => $record->from,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('received_at', 'desc')
            ->poll('10s');
    }
}
