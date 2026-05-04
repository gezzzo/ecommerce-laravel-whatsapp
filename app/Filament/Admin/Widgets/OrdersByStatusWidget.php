<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OrdersByStatusWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '📦 ' . __('Orders by Status');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->selectRaw('
                        MIN(id) as id,
                        delivery_status,
                        COUNT(*) as total_orders,
                        SUM(total) as total_revenue,
                        AVG(total) as avg_order_value,
                        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count
                    ')
                    ->groupBy('delivery_status')
                    ->orderByDesc('total_orders')
            )
            ->columns([
                TextColumn::make('delivery_status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => '⏳ ' . __('Pending'),
                        'processing' => '🔄 ' . __('Processing'),
                        'shipped' => '🚚 ' . __('Shipped'),
                        'delivered' => '✅ ' . __('Delivered'),
                        'cancelled' => '❌ ' . __('Cancelled'),
                        default => ucfirst($state),
                    }),

                TextColumn::make('total_orders')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('today_count')
                    ->label(__('Today'))
                    ->numeric()
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_revenue')
                    ->label(__('Total Revenue'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->sortable(),

                TextColumn::make('avg_order_value')
                    ->label(__('Avg. Value'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
