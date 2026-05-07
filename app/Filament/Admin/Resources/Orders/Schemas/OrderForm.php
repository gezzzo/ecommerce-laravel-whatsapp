<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use App\Enums\DeliveryStatusEnum;
use App\Models\Coupons;
use App\Models\DeliveryCompany;
use App\Models\DeliveryZone;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Order Details'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('Customer'))
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('order_number')
                            ->label(__('Order Number'))
                            ->required()
                            ->maxLength(255),
                        Select::make('delivery_status')
                            ->label(__('Delivery Status'))
                            ->options(DeliveryStatusEnum::getOptions())
                          ,
                        Toggle::make('manual_delivery_status')
                            ->label(__('Manual Delivery Status'))
                            ->helperText(__('Enable this to manage the delivery status manually.')),
                        Select::make('payment_method')
                            ->label(__('Payment Method'))
                            ->options([
                                'cod' => __('Cash on Delivery'),
                                'card' => __('Credit Card'),
                                'wallet' => __('Wallet'),
                            ])
                            ->default('cod')
                            ->required(),
                        Select::make('payment_status')
                            ->label(__('Payment Status'))
                            ->options([
                                'not_paid' => __('Not Paid'),
                                'paid' => __('Paid'),
                            ])
                            ->default('not_paid')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make(__('Customer Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('whatsapp_phone')
                            ->label(__('WhatsApp phone'))
                            ->tel()
                            ->maxLength(255),
                        DateTimePicker::make('whatsapp_confirmed_at')
                            ->label(__('WhatsApp confirmed at')),
                    ])
                    ->columns(2),
                Section::make(__('Shipping Details'))
                    ->schema([
                        TextInput::make('address')
                            ->label(__('Shipping Address'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label(__('City'))
                            ->required()
                            ->maxLength(255),
                        Select::make('delivery_zone_id')
                            ->label(__('Delivery Zone'))
                            ->relationship(
                                'deliveryZone',
                                'city',
                                fn (Builder $query): Builder => $query->with('company.provider'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (DeliveryZone $record): string => sprintf(
                                    '%s - %s %s',
                                    $record->city,
                                    rtrim(rtrim(number_format((float) $record->delivery_fee, 2), '0'), '.'),
                                    __('MAD'),
                                ),
                            )
                            ->searchable()
                            ->preload(),
                        Select::make('delivery_company_id')
                            ->label(__('Delivery Company'))
                            ->relationship(
                                'deliveryCompany',
                                'id',
                                fn (Builder $query): Builder => $query->with('provider'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (DeliveryCompany $record): string => $record->provider?->name
                                    ?? __('Delivery Company').' #'.$record->getKey(),
                            )
                            ->searchable()
                            ->preload(),
                        TextInput::make('shipping')
                            ->label(__('Shipping Cost'))
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix(__('MAD'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                self::refreshCouponTotals($get, $set);
                            }),
                        TextInput::make('tracking_number')
                            ->label(__('Tracking Number'))
                            ->maxLength(255),
                        Textarea::make('comment')
                            ->label(__('Delivery Notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('Pricing'))
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(__('Subtotal'))
                            ->required()
                            ->numeric()
                            ->prefix(__('MAD'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                self::refreshCouponTotals($get, $set);
                            }),
                        Select::make('coupon_id')
                            ->label(__('Coupon'))
                            ->relationship(
                                'coupon',
                                'code',
                                fn (Builder $query): Builder => $query->available(),
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                self::refreshCouponTotals($get, $set);
                            }),
                        TextInput::make('discount')
                            ->label(__('Discount'))
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix(__('MAD'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                self::refreshTotal($get, $set);
                            }),
                        TextInput::make('total')
                            ->label(__('Total'))
                            ->required()
                            ->numeric()
                            ->prefix(__('MAD')),
                        TextInput::make('coupon_code')
                            ->label(__('Coupon Code'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    private static function refreshCouponTotals(Get $get, Set $set): void
    {
        $subtotal = (float) ($get('subtotal') ?? 0);
        $shipping = (float) ($get('shipping') ?? 0);
        $couponId = $get('coupon_id');
        $discount = 0.0;

        if (filled($couponId)) {
            $coupon = Coupons::find($couponId);

            if ($coupon) {
                $discount = $coupon->discountFor($subtotal);
                $set('coupon_code', $coupon->code);
            }
        } else {
            $set('coupon_code', null);
        }

        $set('discount', $discount);
        $set('total', self::calculateTotal($subtotal, $shipping, $discount));
    }

    private static function refreshTotal(Get $get, Set $set): void
    {
        $set('total', self::calculateTotal(
            (float) ($get('subtotal') ?? 0),
            (float) ($get('shipping') ?? 0),
            (float) ($get('discount') ?? 0),
        ));
    }

    private static function calculateTotal(float $subtotal, float $shipping, float $discount): float
    {
        return max(0, $subtotal + $shipping - $discount);
    }
}
