<?php

namespace App\Filament\Developer\DeliveryProviders;

use App\Filament\Developer\DeliveryProviders\Pages\CreateDeliveryProvider;
use App\Filament\Developer\DeliveryProviders\Pages\EditDeliveryProvider;
use App\Filament\Developer\DeliveryProviders\Pages\ListDeliveryProviders;
use App\Filament\Developer\DeliveryProviders\Schemas\DeliveryProviderForm;
use App\Filament\Developer\DeliveryProviders\Tables\DeliveryProvidersTable;
use App\Models\DeliveryProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryProviderResource extends Resource
{
    protected static ?string $model = DeliveryProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Delivery Providers');
    }

    public static function getModelLabel(): string
    {
        return __('Delivery Provider');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Delivery Providers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Delivery');
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryProvidersTable::configure($table);
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
            'index' => ListDeliveryProviders::route('/'),
            'create' => CreateDeliveryProvider::route('/create'),
            'edit' => EditDeliveryProvider::route('/{record}/edit'),
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
