<?php

namespace App\Filament\Admin\Resources\Colors;

use App\Filament\Admin\Resources\Colors\Pages\CreateColor;
use App\Filament\Admin\Resources\Colors\Pages\EditColor;
use App\Filament\Admin\Resources\Colors\Pages\ListColors;
use App\Filament\Admin\Resources\Colors\Schemas\ColorForm;
use App\Filament\Admin\Resources\Colors\Tables\ColorsTable;
use App\Models\Color;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Colors');
    }

    public static function getModelLabel(): string
    {
        return __('Color');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Colors');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return ColorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColorsTable::configure($table);
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
            'index' => ListColors::route('/'),
            'create' => CreateColor::route('/create'),
            'edit' => EditColor::route('/{record}/edit'),
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
