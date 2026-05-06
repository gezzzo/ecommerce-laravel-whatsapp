<?php

namespace App\Filament\Admin\Resources\OrderReturns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label(__('Order'))
                    ->relationship('order', 'order_number')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('order_item_id')
                    ->label(__('Item'))
                    ->relationship('orderItem', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('refund_amount')
                    ->label(__('Refund Amount'))
                    ->required()
                    ->numeric()
                    ->prefix(__('MAD')),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        'completed' => __('Completed'),
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('reason')
                    ->label(__('Reason'))
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->label(__('Admin Notes'))
                    ->columnSpanFull(),
                Toggle::make('inventory_restored')
                    ->label(__('Inventory Restored'))
                    ->default(false),
                DateTimePicker::make('processed_at')
                    ->label(__('Processed At')),
            ]);
    }
}
