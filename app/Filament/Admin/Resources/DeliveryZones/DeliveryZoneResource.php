<?php

namespace App\Filament\Admin\Resources\DeliveryZones;

use App\Filament\Admin\Resources\DeliveryZones\Pages\CreateDeliveryZone;
use App\Filament\Admin\Resources\DeliveryZones\Pages\EditDeliveryZone;
use App\Filament\Admin\Resources\DeliveryZones\Pages\ListDeliveryZones;
use App\Filament\Admin\Resources\DeliveryZones\Schemas\DeliveryZoneForm;
use App\Filament\Admin\Resources\DeliveryZones\Tables\DeliveryZonesTable;
use App\Models\DeliveryZone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryZoneResource extends Resource
{
    protected static ?string $model = DeliveryZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'city';

    public static function getNavigationLabel(): string
    {
        return __('Delivery Zones');
    }

    public static function getModelLabel(): string
    {
        return __('Delivery Zone');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Delivery Zones');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Delivery');
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryZoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryZonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryZones::route('/'),
            'create' => CreateDeliveryZone::route('/create'),
            'edit' => EditDeliveryZone::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
