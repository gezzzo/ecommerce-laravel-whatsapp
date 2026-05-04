<?php

namespace App\Filament\Admin\Resources\Coupons;

use App\Filament\Admin\Resources\Coupons\Pages\CreateCoupons;
use App\Filament\Admin\Resources\Coupons\Pages\EditCoupons;
use App\Filament\Admin\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Admin\Resources\Coupons\Schemas\CouponsForm;
use App\Filament\Admin\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupons;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponsResource extends Resource
{
    protected static ?string $model = Coupons::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationLabel(): string
    {
        return __('Coupons');
    }

    public static function getModelLabel(): string
    {
        return __('Coupon');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Coupons');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Orders');
    }

    public static function form(Schema $schema): Schema
    {
        return CouponsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupons::route('/create'),
            'edit' => EditCoupons::route('/{record}/edit'),
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
