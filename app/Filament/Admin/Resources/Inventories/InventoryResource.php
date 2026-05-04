<?php

namespace App\Filament\Admin\Resources\Inventories;

use App\Filament\Admin\Resources\Inventories\Pages\ListInventories;
use App\Filament\Admin\Resources\Inventories\Pages\ViewInventory;
use App\Filament\Admin\Resources\Inventories\RelationManagers\MovementsRelationManager;
use App\Filament\Admin\Resources\Inventories\Tables\InventoriesTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
    protected static ?int $navigationSort = 3;
    public static function getNavigationLabel(): string
    {
        return __('Inventory');
    }

    public static function getModelLabel(): string
    {
        return __('Inventory Item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Inventory Items');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function table(Table $table): Table
    {
        return InventoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventories::route('/'),
            'view' => ViewInventory::route('/{record}'),
        ];
    }
}
