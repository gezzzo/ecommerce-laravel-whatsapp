<?php

namespace App\Filament\Admin\Pages;

use App\Models\StoreSetting;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

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

    public function getTitle(): string|Htmlable
    {
        return __('Store Settings');
    }

    public function mount(): void
    {
        $promoBanner = StoreSetting::promoBanner();
        $contactInfo = StoreSetting::contactInfo();

        $this->form->fill([
            'checkout_mode' => StoreSetting::get('checkout_mode', StoreSetting::CHECKOUT_OPTIONAL),
            'store_name' => StoreSetting::storeName(),
            'shipping_mode' => StoreSetting::get('shipping_mode', StoreSetting::SHIPPING_PAID),
            'free_shipping_threshold' => StoreSetting::get('free_shipping_threshold', 200),
            'free_shipping_item_count' => StoreSetting::get('free_shipping_item_count', 3),
            'announcement_bar_text' => StoreSetting::announcementBarText(),
            'home_features' => StoreSetting::homeFeatures(),
            'promo_banner_badge' => $promoBanner['badge'],
            'promo_banner_title' => $promoBanner['title'],
            'promo_banner_subtitle' => $promoBanner['subtitle'],
            'contact_phone' => $contactInfo['phone'],
            'contact_email' => $contactInfo['email'],
            'contact_working_hours' => $contactInfo['working_hours'],
            'contact_facebook_url' => $contactInfo['facebook_url'],
            'contact_instagram_url' => $contactInfo['instagram_url'],
            'contact_twitter_url' => $contactInfo['twitter_url'],
            'meta_pixel_id' => StoreSetting::metaPixelId(),
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
                                StoreSetting::CHECKOUT_REQUIRED => '🔒 '.__('Login required'),
                                StoreSetting::CHECKOUT_OPTIONAL => '👤 '.__('Login optional (Guest allowed)'),
                                StoreSetting::CHECKOUT_GUEST => '📦 '.__('Shipping info only (no account)'),
                            ])
                            ->descriptions([
                                StoreSetting::CHECKOUT_REQUIRED => __('Customer must log in before checkout.'),
                                StoreSetting::CHECKOUT_OPTIONAL => __('Customer may log in or continue as guest.'),
                                StoreSetting::CHECKOUT_GUEST => __('Customer enters shipping details directly without an account.'),
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

                Section::make(__('Homepage Content'))
                    ->description(__('Manage public homepage texts.'))
                    ->schema([
                        Textarea::make('announcement_bar_text')
                            ->label(__('Announcement bar text'))
                            ->helperText(__('Shown at the very top of the storefront. Leave empty to hide it.'))
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Repeater::make('home_features')
                            ->label(__('Feature bar items'))
                            ->schema([
                                TextInput::make('icon')
                                    ->label(__('Icon'))
                                    ->maxLength(20),
                                TextInput::make('title')
                                    ->label(__('Title'))
                                    ->required()
                                    ->maxLength(80),
                                TextInput::make('subtitle')
                                    ->label(__('Subtitle'))
                                    ->maxLength(120),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->maxItems(6)
                            ->reorderable()
                            ->columnSpanFull(),

                        TextInput::make('promo_banner_badge')
                            ->label(__('Promo badge'))
                            ->maxLength(80),
                        TextInput::make('promo_banner_title')
                            ->label(__('Promo title'))
                            ->required()
                            ->maxLength(120),
                        Textarea::make('promo_banner_subtitle')
                            ->label(__('Promo subtitle'))
                            ->rows(2)
                            ->maxLength(240)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Contact Information'))
                    ->description(__('Manage public contact details.'))
                    ->schema([
                        TextInput::make('contact_phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('contact_email')
                            ->label(__('Email address'))
                            ->email()
                            ->maxLength(150),
                        TextInput::make('contact_working_hours')
                            ->label(__('Working Hours'))
                            ->maxLength(150),
                        TextInput::make('contact_facebook_url')
                            ->label(__('Facebook URL'))
                            ->placeholder('https://facebook.com/your-store')
                            ->maxLength(255),
                        TextInput::make('contact_instagram_url')
                            ->label(__('Instagram URL'))
                            ->placeholder('https://instagram.com/your-store')
                            ->maxLength(255),
                        TextInput::make('contact_twitter_url')
                            ->label(__('Twitter URL'))
                            ->placeholder('https://x.com/your-store')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make(__('Shipping Settings'))
                    ->description(__('Control customer shipping cost policy.'))
                    ->schema([
                        Radio::make('shipping_mode')
                            ->label(__('Shipping Mode'))
                            ->options([
                                StoreSetting::SHIPPING_FREE => '🎁 '.__('Always free shipping'),
                                StoreSetting::SHIPPING_PAID => '💰 '.__('Paid shipping (by delivery zone)'),
                                StoreSetting::SHIPPING_FREE_AFTER_AMOUNT => '🏷️ '.__('Free after amount'),
                                StoreSetting::SHIPPING_FREE_AFTER_ITEMS => '📦 '.__('Free after item count'),
                            ])
                            ->descriptions([
                                StoreSetting::SHIPPING_FREE => __('All orders ship free without any condition.'),
                                StoreSetting::SHIPPING_PAID => __('Customer pays shipping cost based on selected zone.'),
                                StoreSetting::SHIPPING_FREE_AFTER_AMOUNT => __('Shipping is free once subtotal reaches this amount.'),
                                StoreSetting::SHIPPING_FREE_AFTER_ITEMS => __('Shipping is free once cart item count reaches this number.'),
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

                Section::make(__('Tracking Pixels'))
                    ->description(__('Connect marketing pixels used for ads and conversion tracking.'))
                    ->schema([
                        TextInput::make('meta_pixel_id')
                            ->label(__('Meta Pixel ID'))
                            ->placeholder('123456789012345')
                            ->helperText(__('Paste the numeric Pixel ID from Meta Events Manager. Leave empty to disable it.'))
                            ->maxLength(32)
                            ->rules(['nullable', 'regex:/^\d{5,32}$/'])
                            ->columnSpanFull(),
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
        StoreSetting::set('announcement_bar_text', $data['announcement_bar_text'] ?? '', 'string', 'homepage');
        StoreSetting::set('home_features', $data['home_features'] ?? StoreSetting::DEFAULT_HOME_FEATURES, 'json', 'homepage');
        StoreSetting::set('promo_banner_badge', $data['promo_banner_badge'] ?? '', 'string', 'homepage');
        StoreSetting::set('promo_banner_title', $data['promo_banner_title'] ?? '', 'string', 'homepage');
        StoreSetting::set('promo_banner_subtitle', $data['promo_banner_subtitle'] ?? '', 'string', 'homepage');
        StoreSetting::set('contact_phone', $data['contact_phone'] ?? '', 'string', 'contact');
        StoreSetting::set('contact_email', $data['contact_email'] ?? '', 'string', 'contact');
        StoreSetting::set('contact_working_hours', $data['contact_working_hours'] ?? '', 'string', 'contact');
        StoreSetting::set('contact_facebook_url', $data['contact_facebook_url'] ?? '', 'string', 'contact');
        StoreSetting::set('contact_instagram_url', $data['contact_instagram_url'] ?? '', 'string', 'contact');
        StoreSetting::set('contact_twitter_url', $data['contact_twitter_url'] ?? '', 'string', 'contact');
        StoreSetting::set('meta_pixel_id', $data['meta_pixel_id'] ?? '', 'string', 'tracking');

        Notification::make()
            ->title(__('Settings saved successfully').' ✅')
            ->success()
            ->send();
    }
}
