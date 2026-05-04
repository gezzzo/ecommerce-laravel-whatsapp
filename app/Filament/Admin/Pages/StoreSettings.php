<?php

namespace App\Filament\Admin\Pages;

use App\Models\StoreSetting;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.admin.pages.store-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Store Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Store Settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'checkout_mode'           => StoreSetting::get('checkout_mode', StoreSetting::CHECKOUT_OPTIONAL),
            'store_name'              => StoreSetting::get('store_name', 'متجري'),
            'shipping_mode'           => StoreSetting::get('shipping_mode', StoreSetting::SHIPPING_PAID),
            'free_shipping_threshold' => StoreSetting::get('free_shipping_threshold', 200),
            'free_shipping_item_count' => StoreSetting::get('free_shipping_item_count', 3),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Payment and Registration Settings'))
                    ->description(__('Control checkout access.'))
                    ->schema([
                        Radio::make('checkout_mode')
                            ->label(__('Checkout Mode'))
                            ->options([
                                StoreSetting::CHECKOUT_REQUIRED => '🔒 ' . __('Login required'),
                                StoreSetting::CHECKOUT_OPTIONAL => '👤 ' . __('Login optional (Guest allowed)'),
                                StoreSetting::CHECKOUT_GUEST    => '📦 ' . __('Shipping info only (no account)'),
                            ])
                            ->descriptions([
                                StoreSetting::CHECKOUT_REQUIRED => __('Customer must log in before checkout.'),
                                StoreSetting::CHECKOUT_OPTIONAL => __('Customer may log in or continue as guest.'),
                                StoreSetting::CHECKOUT_GUEST    => __('Customer enters shipping details directly without an account.'),
                            ])
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('General Store Settings'))
                    ->schema([
                        TextInput::make('store_name')
                            ->label(__('Store Name'))
                            ->required()
                            ->maxLength(100),
                    ]),

                Section::make(__('Shipping Settings'))
                    ->description(__('Control customer shipping cost policy.'))
                    ->schema([
                        Radio::make('shipping_mode')
                            ->label(__('Shipping Mode'))
                            ->options([
                                StoreSetting::SHIPPING_FREE             => '🎁 ' . __('Always free shipping'),
                                StoreSetting::SHIPPING_PAID             => '💰 ' . __('Paid shipping (by delivery zone)'),
                                StoreSetting::SHIPPING_FREE_AFTER_AMOUNT => '🏷️ ' . __('Free after amount'),
                                StoreSetting::SHIPPING_FREE_AFTER_ITEMS  => '📦 ' . __('Free after item count'),
                            ])
                            ->descriptions([
                                StoreSetting::SHIPPING_FREE              => __('All orders ship free without any condition.'),
                                StoreSetting::SHIPPING_PAID              => __('Customer pays shipping cost based on selected zone.'),
                                StoreSetting::SHIPPING_FREE_AFTER_AMOUNT => __('Shipping is free once subtotal reaches this amount.'),
                                StoreSetting::SHIPPING_FREE_AFTER_ITEMS  => __('Shipping is free once cart item count reaches this number.'),
                            ])
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('free_shipping_threshold')
                            ->label(__('Free Shipping Threshold (MAD)'))
                            ->helperText(__('Free shipping starts when subtotal reaches this amount.'))
                            ->numeric()
                            ->minValue(0)
                            ->suffix(__('MAD'))
                            ->required()
                            ->visible(fn ($get): bool => $get('shipping_mode') === StoreSetting::SHIPPING_FREE_AFTER_AMOUNT),

                        TextInput::make('free_shipping_item_count')
                            ->label(__('Free Shipping Item Count'))
                            ->helperText(__('Free shipping starts when cart item count reaches this number.'))
                            ->numeric()
                            ->minValue(1)
                            ->suffix(__('Items'))
                            ->required()
                            ->visible(fn ($get): bool => $get('shipping_mode') === StoreSetting::SHIPPING_FREE_AFTER_ITEMS),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        StoreSetting::set('checkout_mode', $data['checkout_mode'], 'string', 'checkout');
        StoreSetting::set('store_name', $data['store_name'], 'string', 'general');
        StoreSetting::set('shipping_mode', $data['shipping_mode'], 'string', 'shipping');
        StoreSetting::set('free_shipping_threshold', $data['free_shipping_threshold'] ?? 200, 'integer', 'shipping');
        StoreSetting::set('free_shipping_item_count', $data['free_shipping_item_count'] ?? 3, 'integer', 'shipping');

        Notification::make()
            ->title(__('Settings saved successfully') . ' ✅')
            ->success()
            ->send();
    }
}
