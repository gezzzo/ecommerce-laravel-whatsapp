<?php

namespace App\Filament\Admin\Resources\WhatsappCampaigns\Schemas;

use App\Enums\WhatsappMediaType;
use App\Models\WhatsappSession;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsappCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Campaign Details'))
                ->icon('heroicon-o-megaphone')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('Campaign Name'))
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull()
                        ->placeholder(__('e.g. Eid Sale 2026')),

                    Select::make('whatsapp_session_id')
                        ->label(__('WhatsApp Session'))
                        ->options(
                            WhatsappSession::where('status', 'connected')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->columnSpanFull()
                        ->helperText(__('Only connected sessions are shown.')),

                    Textarea::make('message')
                        ->label(__('Message Text'))
                        ->required()
                        ->rows(5)
                        ->maxLength(4000)
                        ->columnSpanFull()
                        ->placeholder(__('Write your campaign message here...')),

                    TextInput::make('delay_seconds')
                        ->label(__('Delay Between Messages (seconds)'))
                        ->numeric()
                        ->default(3)
                        ->minValue(1)
                        ->maxValue(60)
                        ->suffix(__('sec'))
                        ->helperText(__('Recommended: 3-10 seconds to avoid being blocked.')),

                    DateTimePicker::make('scheduled_at')
                        ->label(__('Schedule At'))
                        ->nullable()
                        ->helperText(__('Leave empty to send manually via Launch button.')),
                ]),

            Section::make(__('Media Attachment'))
                ->icon('heroicon-o-paper-clip')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Select::make('media_type')
                        ->label(__('Media Type'))
                        ->options(collect(WhatsappMediaType::cases())->mapWithKeys(
                            fn ($case) => [$case->value => $case->label()]
                        ))
                        ->default(WhatsappMediaType::None->value)
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('media_url')
                        ->label(__('Media URL'))
                        ->url()
                        ->nullable()
                        ->columnSpanFull()
                        ->placeholder(__('https://example.com/image.jpg'))
                        ->visible(fn ($get) => $get('media_type') !== WhatsappMediaType::None->value)
                        ->helperText(__('Public URL to the media file.')),

                    TextInput::make('media_caption')
                        ->label(__('Media Caption'))
                        ->nullable()
                        ->maxLength(500)
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('media_type') !== WhatsappMediaType::None->value),
                ]),

            Section::make(__('Contacts'))
                ->icon('heroicon-o-users')
                ->description(__('Add the phone numbers to send this campaign to.'))
                ->schema([
                    Repeater::make('contacts')
                        ->relationship()
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('phone')
                                    ->label(__('Phone Number'))
                                    ->required()
                                    ->tel()
                                    ->placeholder('+201234567890'),

                                TextInput::make('name')
                                    ->label(__('Name (optional)'))
                                    ->nullable()
                                    ->placeholder(__('Ahmed')),

                                TextInput::make('sort_order')
                                    ->label(__('Order'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                        ])
                        ->orderColumn('sort_order')
                        ->addActionLabel(__('Add Contact'))
                        ->collapsible()
                        ->reorderable()
                        ->columns(1),
                ]),
        ]);
    }
}
