<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopCitiesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'half';

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '🗺️ ' . __('Top Cities by Sales');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->selectRaw('
                        MIN(id) as id,
                        city,
                        COUNT(*) as total_orders,
                        SUM(total) as total_revenue,
                        AVG(total) as avg_order_value
                    ')
                    ->whereNotNull('city')
                    ->whereNot('city', '')
                    ->groupBy('city')
                    ->orderByDesc('total_orders')
            )
            ->columns([
                TextColumn::make('city')
                    ->label(__('City'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('total_orders')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_revenue')
                    ->label(__('Revenue'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->sortable(),

                TextColumn::make('avg_order_value')
                    ->label(__('Avg.'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->color('gray'),
            ])
            ->defaultSort('total_orders', 'desc')
            ->paginated([5, 10, 25]);
    }
}
