<?php

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Enums\DeliveryStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\Delivery\OrderDeliveryService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('Order Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('whatsapp_confirmation_status')
                    ->label(__('WhatsApp confirmation'))
                    ->state(fn (Order $record): string => $record->whatsapp_confirmed_at ? __('Confirmed') : __('Waiting'))
                    ->badge()
                    ->color(fn (Order $record): string => $record->whatsapp_confirmed_at ? 'success' : 'warning'),
                TextColumn::make('whatsapp_phone')
                    ->label(__('WhatsApp phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->label(__('City'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label(__('Delivery Status'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => __('Pending'),
                        'processing' => __('Processing'),
                        'shipped' => __('Shipped'),
                        'delivered' => __('Delivered'),
                        'cancelled' => __('Cancelled'),
                        default => $state ?? '',
                    })
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->delivery_status) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_method')
                    ->label(__('Payment Method'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cod' => __('Cash on Delivery'),
                        'card' => __('Credit Card'),
                        'wallet' => __('Wallet'),
                        default => $state ?? '',
                    })
                    ->badge(),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('MAD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label(__('Delivery Status'))
                    ->options(OrderStatusEnum::getOptions())
                    ->placeholder(__('order.filters.all_statuses')),

                SelectFilter::make('delivery_status')
                    ->label(__('order.filters.delivery_status'))
                    ->options(DeliveryStatusEnum::getOptions())
                    ->placeholder(__('order.filters.all_delivery_statuses')),

                SelectFilter::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'cod' => __('Cash on Delivery'),
                        'card' => __('Credit Card'),
                        'wallet' => __('Wallet'),
                    ]),
            ])
            ->recordActions([

                Action::make('whatsapp')
                    ->label(__('order.fields.whatsapp_text'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function (Order $record) {
                        $phone = $record->phone ?? $record->customer_phone;
                        if (! $phone) {
                            return null;
                        }

                        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
                        if (! str_starts_with($cleanPhone, '+') && ! str_starts_with($cleanPhone, '00')) {
                            $cleanPhone = '+212'.ltrim($cleanPhone, '0');
                        }

                        $message = urlencode('مرحباً، بخصوص طلبكم رقم: '.($record->order_number ?? $record->id));

                        return "https://wa.me/{$cleanPhone}?text={$message}";
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Order $record): bool => ! empty($record->phone) || ! empty($record->customer_phone)),

                Action::make('phone_call')
                    ->label(__('order.fields.phone_call'))
                    ->icon('heroicon-o-phone')
                    ->color('primary')
                    ->url(function (Order $record) {
                        $phone = $record->phone ?? $record->customer_phone;
                        if (! $phone) {
                            return null;
                        }

                        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
                        if (! str_starts_with($cleanPhone, '+') && ! str_starts_with($cleanPhone, '00')) {
                            $cleanPhone = '+212'.ltrim($cleanPhone, '0');
                        }

                        return "tel:{$cleanPhone}";
                    })
                    ->visible(fn (Order $record): bool => ! empty($record->phone) || ! empty($record->customer_phone)),

                Action::make('send_to_delivery')
                    ->label(__('order.actions.send_to_delivery'))
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (Order $record): bool => ! $record->tracking_number &&
                        $record->delivery_zone_id
                    )
                    ->action(function (Order $record) {
                        try {
                            $dispatch = app(OrderDeliveryService::class)->sendToActiveCompany($record);
                            $order = $dispatch['order'];
                            $companyName = $dispatch['company']->provider?->name ?? __('Delivery Company');

                            Notification::make()
                                ->title(__('order.messages.delivery_sent_title'))
                                ->body(__('order.messages.delivery_sent_to_company_body', [
                                    'company' => $companyName,
                                    'tracking_number' => $order->tracking_number ?? __('order.placeholders.not_specified'),
                                ]))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            $cityId = $record->deliveryZone?->external_city_id;
                            $message = $e->getMessage();

                            if ($cityId) {
                                $message .= "\n".__('order.messages.delivery_city_id', ['city_id' => $cityId]);
                            }

                            Notification::make()
                                ->title(__('order.messages.delivery_send_failed_title'))
                                ->body($message)
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('order.modals.send_delivery_heading'))
                    ->modalDescription(__('order.modals.send_delivery_description'))
                    ->modalSubmitActionLabel(__('order.modals.send_delivery_confirm')),

                Action::make('track_delivery')
                    ->label(__('order.actions.track_delivery'))
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->tracking_number &&
                        $record->delivery_company_id
                    )
                    ->action(function (Order $record) {
                        try {
                            $status = app(OrderDeliveryService::class)->track($record);

                            Notification::make()
                                ->title(__('order.messages.delivery_tracked_title'))
                                ->body(__('order.messages.delivery_tracked_body', ['status' => $status]))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('order.messages.delivery_track_failed_title'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('tracking_history')
                    ->label(__('order.actions.tracking_history'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->tracking_number !== null)
                    ->modalHeading(__('order.modals.tracking_history_heading'))
                    ->modalContent(function (Order $record) {
                        $trackingRecords = $record->trackingParcels()
                            ->orderBy('time', 'desc')
                            ->orderBy('id', 'desc')
                            ->get();

                        if ($trackingRecords->isEmpty()) {
                            return view('filament.modals.no-tracking-data');
                        }

                        return view('filament.modals.tracking-history', [
                            'trackingRecords' => $trackingRecords,
                            'order' => $record,
                        ]);
                    })
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('order.actions.close')),

                Action::make('change_status')
                    ->label(__('order.actions.change_status'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Select::make('new_status')
                            ->label(__('order.fields.status'))
                            ->options(OrderStatusEnum::getOptions())
                            ->required()
                            ->default(fn (Order $record) => $record->status),
                        Textarea::make('comment')
                            ->label(__('order.fields.comment'))
                            ->placeholder(__('order.placeholders.status_change_reason'))
                            ->rows(3),
                    ])
                    ->action(function (Order $record, array $data) {
                        $oldStatus = $record->status;
                        $newStatus = $data['new_status'];

                        if ($oldStatus !== $newStatus) {
                            $record->update(['status' => $newStatus]);

                            // Save to order history
                            $record->orderHistories()->create([
                                'user_id' => auth()->id(),
                                'action_type' => 'status_changed',
                                'old_value' => $oldStatus,
                                'new_value' => $newStatus,
                                'comment' => $data['comment'] ?? null,
                            ]);

                            Notification::make()
                                ->title(__('order.messages.status_updated'))
                                ->body(__('order.messages.status_updated_from_to', [
                                    'old' => $oldStatus,
                                    'new' => $newStatus,
                                ]))
                                ->success()
                                ->send();
                        }
                    })
                    ->modalHeading(__('order.modals.change_status_heading'))
                    ->modalSubmitActionLabel(__('order.actions.update_status')),

                Action::make('change_delivery_status')
                    ->label(__('order.actions.change_delivery_status'))
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => $record->tracking_number === null)
                    ->form([
                        Select::make('new_delivery_status')
                            ->label(__('order.fields.delivery_status'))
                            ->options(DeliveryStatusEnum::getOptions())
                            ->required()
                            ->default(fn (Order $record) => $record->delivery_status),
                        Textarea::make('comment')
                            ->label(__('order.fields.comment'))
                            ->placeholder(__('order.placeholders.delivery_status_change_reason'))
                            ->rows(3),
                    ])
                    ->action(function (Order $record, array $data) {
                        $oldStatus = $record->delivery_status;
                        $newStatus = $data['new_delivery_status'];

                        if ($oldStatus !== $newStatus) {
                            $record->update(['delivery_status' => $newStatus]);

                            // Save to order history
                            $record->orderHistories()->create([
                                'user_id' => auth()->id(),
                                'action_type' => 'delivery_status_changed',
                                'old_value' => $oldStatus,
                                'new_value' => $newStatus,
                                'comment' => $data['comment'] ?? null,
                            ]);

                            Notification::make()
                                ->title(__('order.messages.delivery_status_updated'))
                                ->body(__('order.messages.delivery_status_updated_from_to', [
                                    'old' => $oldStatus ?? __('order.placeholders.not_specified'),
                                    'new' => $newStatus,
                                ]))
                                ->success()
                                ->send();
                        }
                    })
                    ->modalHeading(__('order.modals.change_delivery_status_heading'))
                    ->modalSubmitActionLabel(__('order.actions.update_delivery_status')),

                Action::make('add_comment')
                    ->label(__('order.actions.add_comment'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('gray')
                    ->form([
                        Textarea::make('comment')
                            ->label(__('order.fields.comment'))
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (Order $record, array $data) {
                        // Save to order history
                        $record->orderHistories()->create([
                            'user_id' => auth()->id(),
                            'action_type' => 'comment_added',
                            'old_value' => null,
                            'new_value' => null,
                            'comment' => $data['comment'],
                        ]);

                        Notification::make()
                            ->title(__('order.messages.comment_added'))
                            ->success()
                            ->send();
                    })
                    ->modalHeading(__('order.modals.add_comment_heading'))
                    ->modalSubmitActionLabel(__('order.actions.add_comment')),

                ViewAction::make(),
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
