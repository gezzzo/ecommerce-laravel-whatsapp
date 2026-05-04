<?php

namespace App\Filament\Admin\Resources\WhatsappSessions\Schemas;

use App\Enums\WhatsappSessionStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsappSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('MegaMsg Instance Credentials'))
                ->icon('heroicon-o-device-phone-mobile')
                ->description(__('Create an instance on megamsg.app dashboard, then paste the credentials here.'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('Session Name'))
                        ->required()
                        ->maxLength(100)
                        ->columnSpanFull()
                        ->placeholder(__('e.g. Main Store Number')),

                    TextInput::make('instance_id')
                        ->label(__('Instance ID'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(200)
                        ->columnSpanFull()
                        ->placeholder(__('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'))
                        ->helperText(__('Found in your MegaMsg dashboard under Instance settings.')),

                    TextInput::make('api_token')
                        ->label(__('API Token (Bearer)'))
                        ->required()
                        ->password()
                        ->revealable()
                        ->columnSpanFull()
                        ->placeholder(__('64-character token from MegaMsg dashboard'))
                        ->helperText(__('The 64-character token shown in your MegaMsg instance.')),

                    TextInput::make('phone_number')
                        ->label(__('Connected Phone Number'))
                        ->nullable()
                        ->tel()
                        ->placeholder('+201234567890')
                        ->helperText(__('The WhatsApp number linked to this instance (informational only).')),

                    Select::make('status')
                        ->label(__('Status'))
                        ->options(collect(WhatsappSessionStatus::cases())->mapWithKeys(
                            fn ($case) => [$case->value => $case->label()]
                        ))
                        ->default(WhatsappSessionStatus::Connected->value)
                        ->required()
                        ->helperText(__('Set to Connected once you have scanned the QR on MegaMsg dashboard.')),
                ]),
        ]);
    }
}
