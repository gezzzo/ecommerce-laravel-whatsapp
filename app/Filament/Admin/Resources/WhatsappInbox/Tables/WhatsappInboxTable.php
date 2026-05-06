<?php

namespace App\Filament\Admin\Resources\WhatsappInbox\Tables;

use App\Models\WhatsappInboxMessage;
use App\Services\WhatsappInboxReplyService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
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
                    ->label(__('Read'))
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
                    ->label(__('Message Type'))
                    ->options([
                        'text' => __('Text'),
                        'image' => __('Image'),
                        'audio' => __('Audio'),
                        'video' => __('Video'),
                        'document' => __('Document'),
                        'unknown' => __('Other'),
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
                    ->modalHeading(fn (WhatsappInboxMessage $record): string => __('Reply to :phone', [
                        'phone' => $record->from,
                    ]))
                    ->modalDescription(__('This will send one WhatsApp message directly without creating a campaign.'))
                    ->form([
                        Textarea::make('message')
                            ->label(__('Reply message'))
                            ->required()
                            ->rows(5)
                            ->maxLength(4000)
                            ->placeholder(__('Write your reply here...'))
                            ->columnSpanFull(),
                    ])
                    ->modalSubmitActionLabel(__('Send Reply'))
                    ->action(function (WhatsappInboxMessage $record, array $data): void {
                        $result = app(WhatsappInboxReplyService::class)->reply(
                            inboxMessage: $record,
                            message: $data['message'] ?? '',
                        );

                        if ($result['success']) {
                            Notification::make()
                                ->title(__('Reply sent successfully.'))
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('Reply failed'))
                            ->body($result['error'] ?? __('Something went wrong.'))
                            ->danger()
                            ->send();
                    }),
            ])
            ->defaultSort('received_at', 'desc')
            ->poll('10s');
    }
}
