<?php

namespace App\Filament\Admin\Resources\OrderReturns;

use App\Filament\Admin\Resources\OrderReturns\Pages\CreateOrderReturn;
use App\Filament\Admin\Resources\OrderReturns\Pages\EditOrderReturn;
use App\Filament\Admin\Resources\OrderReturns\Pages\ListOrderReturns;
use App\Filament\Admin\Resources\OrderReturns\Schemas\OrderReturnForm;
use App\Filament\Admin\Resources\OrderReturns\Tables\OrderReturnsTable;
use App\Models\OrderReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderReturnResource extends Resource
{
    protected static ?string $model = OrderReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return __('Order Returns');
    }

    public static function getModelLabel(): string
    {
        return __('Order Return');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Order Returns');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Orders');
    }

    public static function form(Schema $schema): Schema
    {
        return OrderReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderReturnsTable::configure($table);
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
            'index' => ListOrderReturns::route('/'),
            'create' => CreateOrderReturn::route('/create'),
            'edit' => EditOrderReturn::route('/{record}/edit'),
        ];
    }
}
