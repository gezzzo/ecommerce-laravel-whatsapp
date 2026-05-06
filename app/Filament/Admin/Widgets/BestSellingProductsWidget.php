<?php

namespace App\Filament\Admin\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellingProductsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected int $defaultPaginationPageOption = 10;

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '🏆 ' . __('Best Selling Products');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBestSellingQuery())
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->width('50px'),

                TextColumn::make('product_name')
                    ->label(__('Product'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('total_qty')
                    ->label(__('Units Sold'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_revenue')
                    ->label(__('Revenue'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('order_count')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('avg_price')
                    ->label(__('Avg. Price'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->color('gray'),
            ])
            ->defaultSort('total_qty', 'desc')
            ->paginated([10, 25, 50]);
    }

    private function getBestSellingQuery(): Builder
    {
        return \App\Models\OrderItem::query()
            ->join('sku_codes', 'order_items.sku_code', '=', 'sku_codes.sku_code')
            ->leftJoin('products', function ($join) {
                $join->on('sku_codes.skuable_id', '=', 'products.id')
                    ->where('sku_codes.skuable_type', '=', 'App\\Models\\Product');
            })
            ->leftJoin('variants', function ($join) {
                $join->on('sku_codes.skuable_id', '=', 'variants.id')
                    ->where('sku_codes.skuable_type', '=', 'App\\Models\\Variant');
            })
            ->leftJoin('products as variant_products', 'variants.product_id', '=', 'variant_products.id')
            ->selectRaw('
                MIN(order_items.id) as id,
                order_items.sku_code as sku,
                COALESCE(products.name, variant_products.name) as product_name,
                SUM(order_items.quantity) as total_qty,
                SUM(order_items.price * order_items.quantity) as total_revenue,
                COUNT(DISTINCT order_items.order_id) as order_count,
                AVG(order_items.price) as avg_price
            ')
            ->whereNull('order_items.deleted_at')
            ->groupBy('order_items.sku_code', 'products.name', 'variant_products.name')
            ->orderByDesc('total_qty');
    }
}
