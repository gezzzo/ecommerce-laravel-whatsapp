<?php

namespace App\Filament\Admin\Resources\WhatsappInbox;

use App\Filament\Admin\Resources\WhatsappInbox\Pages\ListWhatsappInbox;
use App\Filament\Admin\Resources\WhatsappInbox\Tables\WhatsappInboxTable;
use App\Models\WhatsappInboxMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsappInboxResource extends Resource
{
    protected static ?string $model = WhatsappInboxMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;
    protected static ?int $navigationSort = 3;
    public static function getNavigationLabel(): string
    {
        return __('Inbox');
    }

    public static function getModelLabel(): string
    {
        return __('WhatsApp Message');
    }

    public static function getPluralModelLabel(): string
    {
        return __('WhatsApp Messages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('WhatsApp');
    }

    /** Show unread count badge in the sidebar navigation. */
    public static function getNavigationBadge(): ?string
    {
        $count = WhatsappInboxMessage::unread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        // Inbox messages are read-only — no create/edit form needed.
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return WhatsappInboxTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsappInbox::route('/'),
        ];
    }

    /** Disable create button — inbox is read-only. */
    public static function canCreate(): bool
    {
        return false;
    }
}
