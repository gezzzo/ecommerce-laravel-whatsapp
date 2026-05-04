<?php

namespace App\Filament\Admin\Resources\DeliveryCompanies;

use App\Filament\Admin\Resources\DeliveryCompanies\Pages\CreateDeliveryCompany;
use App\Filament\Admin\Resources\DeliveryCompanies\Pages\EditDeliveryCompany;
use App\Filament\Admin\Resources\DeliveryCompanies\Pages\ListDeliveryCompanies;
use App\Filament\Admin\Resources\DeliveryCompanies\Schemas\DeliveryCompanyForm;
use App\Filament\Admin\Resources\DeliveryCompanies\Tables\DeliveryCompaniesTable;
use App\Models\DeliveryCompany;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryCompanyResource extends Resource
{
    protected static ?string $model = DeliveryCompany::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return __('Delivery Companies');
    }

    public static function getModelLabel(): string
    {
        return __('Delivery Company');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Delivery Companies');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Delivery');
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryCompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryCompaniesTable::configure($table);
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
            'index' => ListDeliveryCompanies::route('/'),
            'create' => CreateDeliveryCompany::route('/create'),
            'edit' => EditDeliveryCompany::route('/{record}/edit'),
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
