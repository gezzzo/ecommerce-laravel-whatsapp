<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopCustomersWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'half';

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '👑 ' . __('Top Customers');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->selectRaw('
                        MIN(id) as id,
                        phone,
                        name,
                        COUNT(*) as total_orders,
                        SUM(total) as total_spent,
                        MAX(created_at) as last_order_at
                    ')
                    ->whereNotNull('phone')
                    ->groupBy('phone', 'name')
                    ->orderByDesc('total_spent')
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->weight('bold')
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('total_orders')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_spent')
                    ->label(__('Total Spent'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('last_order_at')
                    ->label(__('Last Order'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('total_spent', 'desc')
            ->paginated([5, 10, 25]);
    }
}
