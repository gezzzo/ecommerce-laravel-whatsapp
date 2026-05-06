<?php

namespace App\Filament\Admin\Resources\Coupons\Schemas;

use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CouponsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('Code'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                        $set('code', Str::upper(trim((string) $state)));
                    }),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'percent' => __('Percent'),
                        'fixed' => __('Fixed'),
                    ])
                    ->default('fixed')
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label(__('Value'))
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix(fn (Get $get): string => $get('type') === 'fixed' ? __('MAD') : '%')
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            if ($get('type') === 'percent' && (float) $value > 100) {
                                $fail('نسبة الخصم لا يمكن أن تكون أكبر من 100%.');
                            }
                        },
                    ]),
                TextInput::make('max_uses')
                    ->label(__('Max Uses'))
                    ->numeric()
                    ->minValue(1)
                    ->default(null),
                TextInput::make('used_count')
                    ->label(__('Used Count'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                DateTimePicker::make('expires_at')
                    ->label(__('Expires At')),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
