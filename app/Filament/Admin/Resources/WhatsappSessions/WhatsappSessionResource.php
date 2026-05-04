<?php

namespace App\Filament\Admin\Resources\WhatsappSessions;

use App\Filament\Admin\Resources\WhatsappSessions\Pages\CreateWhatsappSession;
use App\Filament\Admin\Resources\WhatsappSessions\Pages\EditWhatsappSession;
use App\Filament\Admin\Resources\WhatsappSessions\Pages\ListWhatsappSessions;
use App\Filament\Admin\Resources\WhatsappSessions\Schemas\WhatsappSessionForm;
use App\Filament\Admin\Resources\WhatsappSessions\Tables\WhatsappSessionsTable;
use App\Models\WhatsappSession;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsappSessionResource extends Resource
{
    protected static ?string $model = WhatsappSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('WhatsApp Sessions');
    }

    public static function getModelLabel(): string
    {
        return __('WhatsApp Session');
    }

    public static function getPluralModelLabel(): string
    {
        return __('WhatsApp Sessions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('WhatsApp');
    }

    public static function form(Schema $schema): Schema
    {
        return WhatsappSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsappSessionsTable::configure($table)
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsappSessions::route('/'),
            'create' => CreateWhatsappSession::route('/create'),
            'edit' => EditWhatsappSession::route('/{record}/edit'),
        ];
    }
}
