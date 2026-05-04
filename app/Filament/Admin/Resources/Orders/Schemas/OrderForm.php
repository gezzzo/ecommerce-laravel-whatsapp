<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Order Details'))
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('order_number')
                            ->required()
                            ->maxLength(255),
                        Select::make('delivery_status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Select::make('payment_method')
                            ->options([
                                'cod' => 'COD',
                                'card' => 'Card',
                                'wallet' => 'Wallet',
                            ])
                            ->default('cod')
                            ->required(),
                        Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending'),
                    ])
                    ->columns(2),
                Section::make(__('Customer Information'))
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('whatsapp_phone')
                            ->label(__('WhatsApp phone'))
                            ->tel()
                            ->maxLength(255),
                        DateTimePicker::make('whatsapp_confirmed_at')
                            ->label(__('WhatsApp confirmed at')),
                        TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make(__('Delivery'))
                    ->schema([
                        Select::make('delivery_zone_id')
                            ->relationship('deliveryZone', 'city')
                            ->searchable()
                            ->preload(),
                        Select::make('delivery_company_id')
                            ->relationship('deliveryCompany', 'id')
                            ->searchable()
                            ->preload(),
                        TextInput::make('tracking_number')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make(__('Pricing'))
                    ->schema([
                        TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('shipping')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('discount')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('coupon_code')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
