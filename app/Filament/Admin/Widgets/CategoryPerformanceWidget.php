<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class CategoryPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '🗂️ ' . __('Revenue by Product Category');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Category::query()
                    ->leftJoin('products', 'categories.id', '=', 'products.category_id')
                    ->leftJoin('sku_codes', function ($join) {
                        $join->on('products.id', '=', 'sku_codes.skuable_id')
                            ->where('sku_codes.skuable_type', '=', 'App\\Models\\Product');
                    })
                    ->leftJoin('order_items', 'sku_codes.sku_code', '=', 'order_items.sku_code')
                    ->selectRaw('
                        categories.id,
                        categories.name as category_name,
                        COUNT(DISTINCT products.id) as product_count,
                        COALESCE(SUM(order_items.quantity), 0) as units_sold,
                        COALESCE(SUM(order_items.price * order_items.quantity), 0) as total_revenue,
                        COALESCE(AVG(order_items.price), 0) as avg_price
                    ')
                    ->groupBy('categories.id', 'categories.name')
                    ->orderByDesc('total_revenue')
            )
            ->columns([
                TextColumn::make('category_name')
                    ->label(__('Category'))
                    ->weight('bold')
                    ->icon('heroicon-o-tag'),

                TextColumn::make('product_count')
                    ->label(__('Products'))
                    ->numeric()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('units_sold')
                    ->label(__('Units Sold'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_revenue')
                    ->label(__('Revenue'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('avg_price')
                    ->label(__('Avg. Selling Price'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->color('gray'),

                TextColumn::make('revenue_share')
                    ->label(__('Share %'))
                    ->getStateUsing(function ($record) {
                        $total = \App\Models\OrderItem::sum(DB::raw('price * quantity'));

                        return $total > 0
                            ? round(($record->total_revenue / $total) * 100, 1) . '%'
                            : '0%';
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->paginated(false);
    }
}
