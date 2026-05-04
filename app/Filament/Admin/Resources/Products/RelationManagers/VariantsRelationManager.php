<?php

namespace App\Filament\Admin\Resources\Products\RelationManagers;

use App\Enums\InventoryMovementType;
use App\Models\SkuCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Support\ImageUploadHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('color_id')
                    ->relationship('color', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('size_id')
                    ->relationship('size', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),
                TextInput::make('price_before_discount')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('selling_price')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
                ImageUploadHelper::make('image')
                    ->directory('variants'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('skuCode.sku_code')
                    ->label(__('SKU'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->copyable(),
                TextColumn::make('color.name')
                    ->label(__('Color'))
                    ->sortable(),
                TextColumn::make('size.name')
                    ->label(__('Size'))
                    ->sortable(),
                TextColumn::make('inventory.quantity')
                    ->label(__('Stock'))
                    ->numeric()
                    ->sortable()
                    ->default(0)
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('cost_price')
                    ->money('MAD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('selling_price')
                    ->money('MAD')
                    ->sortable(),
                TextColumn::make('price_before_discount')
                    ->money('MAD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function ($record): void {
                        $variant = $record;
                        $variant->load('color', 'size');
                        $product = $variant->product;

                        // Auto-generate SKU
                        $colorCode = $variant->color
                            ? mb_strtoupper(mb_substr($variant->color->name, 0, 3))
                            : 'NOC';
                        $sizeCode = $variant->size
                            ? mb_strtoupper(mb_substr($variant->size->name, 0, 3))
                            : 'NOS';
                        $prefix = "VAR-{$product->id}-{$colorCode}-{$sizeCode}";
                        $base = $prefix . '-' . str_pad((string) $variant->id, 4, '0', STR_PAD_LEFT);

                        $counter = 0;
                        $sku = $base;
                        while (SkuCode::where('sku_code', $sku)->exists()) {
                            $counter++;
                            $sku = $base . '-' . $counter;
                        }

                        $variant->skuCode()->create(['sku_code' => $sku]);

                        // Create inventory record with 0 stock
                        $variant->inventory()->create(['quantity' => 0]);
                    }),
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
}
