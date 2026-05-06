<?php

namespace App\Filament\Admin\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku_code')
                    ->label(__('SKU'))
                    ->maxLength(255),
                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->prefix(__('MAD')),
                TextInput::make('total')
                    ->label(__('Total'))
                    ->required()
                    ->numeric()
                    ->prefix(__('MAD')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'skuCode.skuable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Product::class => ['category'],
                    Variant::class => ['product.category', 'size', 'color'],
                ]),
            ]))
            ->columns([
                ImageColumn::make('product_image')
                    ->label(__('Image'))
                    ->circular()
                    ->state(fn (OrderItem $record): ?string => $this->productImage($record)),
                TextColumn::make('product_name')
                    ->label(__('Name'))
                    ->state(fn (OrderItem $record): string => $this->productName($record))
                    ->description(fn (OrderItem $record): ?string => $this->variantDescription($record))
                    ->searchable(false),
                TextColumn::make('sku_code')
                    ->label(__('SKU'))
                    ->state(fn (OrderItem $record): string => $record->skuCode?->sku_code ?? (string) $record->sku_code),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('MAD', locale: 'en'),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('MAD', locale: 'en'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function productImage(OrderItem $record): ?string
    {
        $skuable = $record->skuCode?->skuable;

        if ($skuable instanceof Variant) {
            return $skuable->image ?: $skuable->product?->thumbnail;
        }

        if ($skuable instanceof Product) {
            return $skuable->thumbnail;
        }

        return null;
    }

    private function productName(OrderItem $record): string
    {
        $skuable = $record->skuCode?->skuable;

        if ($skuable instanceof Variant) {
            return $skuable->product?->name ?? __('Product unavailable');
        }

        if ($skuable instanceof Product) {
            return $skuable->name;
        }

        return __('Product unavailable');
    }

    private function variantDescription(OrderItem $record): ?string
    {
        $skuable = $record->skuCode?->skuable;

        if (! $skuable instanceof Variant) {
            return null;
        }

        $parts = collect([
            $skuable->color?->name,
            $skuable->size?->name,
        ])->filter();

        return $parts->isNotEmpty() ? $parts->implode(' / ') : null;
    }
}
