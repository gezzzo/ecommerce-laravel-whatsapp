<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Coupons;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CouponPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'half';

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '🎟️ ' . __('Coupon Performance');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Coupons::query()
                    ->leftJoin('orders', 'coupons.id', '=', 'orders.coupon_id')
                    ->selectRaw('
                        coupons.id,
                        coupons.code,
                        coupons.type,
                        coupons.value,
                        coupons.max_uses,
                        coupons.used_count,
                        coupons.is_active,
                        coupons.expires_at,
                        COUNT(orders.id) as order_count,
                        COALESCE(SUM(orders.discount), 0) as total_discount_given,
                        COALESCE(SUM(orders.total), 0) as revenue_with_coupon
                    ')
                    ->groupBy(
                        'coupons.id', 'coupons.code', 'coupons.type', 'coupons.value',
                        'coupons.max_uses', 'coupons.used_count', 'coupons.is_active', 'coupons.expires_at'
                    )
                    ->orderByDesc('order_count')
            )
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'percentage' => '% ' . __('Percent'),
                        'fixed' => __('Fixed'),
                        default => ucfirst($state),
                    }),

                TextColumn::make('value')
                    ->label(__('Value'))
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage'
                        ? $state . '%'
                        : 'MAD ' . number_format($state, 2)
                    ),

                TextColumn::make('order_count')
                    ->label(__('Times Used'))
                    ->numeric()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('usage_rate')
                    ->label(__('Usage Rate'))
                    ->getStateUsing(fn ($record) => $record->max_uses > 0
                        ? round(($record->used_count / $record->max_uses) * 100) . '%'
                        : '∞'
                    )
                    ->badge()
                    ->color(fn ($record) => $record->max_uses > 0 && ($record->used_count / $record->max_uses) >= 0.8 ? 'danger' : 'success'),

                TextColumn::make('total_discount_given')
                    ->label(__('Total Discount'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->color('danger'),

                TextColumn::make('revenue_with_coupon')
                    ->label(__('Revenue Generated'))
                    ->formatStateUsing(fn ($state) => 'MAD ' . number_format($state, 2))
                    ->color('success'),
            ])
            ->paginated([5, 10]);
    }
}
