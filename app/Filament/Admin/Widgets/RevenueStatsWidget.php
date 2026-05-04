<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereNotIn('delivery_status', ['cancelled'])
            ->sum('total');

        $monthRevenue = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereNotIn('delivery_status', ['cancelled'])
            ->sum('total');

        $totalRevenue = Order::whereNotIn('delivery_status', ['cancelled'])
            ->sum('total');

        $totalOrders = Order::count();

        $pendingOrders = Order::where('delivery_status', 'pending')->count();

        $todayOrders = Order::whereDate('created_at', today())->count();

        $totalCustomers = Order::whereNotNull('phone')
            ->distinct('phone')
            ->count('phone');

        $avgOrderValue = $totalOrders > 0
            ? round($totalRevenue / $totalOrders, 2)
            : 0;

        return [
            Stat::make(__('Today\'s Revenue'), 'MAD ' . Number::format($todayRevenue, 2))
                ->description($todayOrders . ' ' . __('orders today'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->last7DaysRevenue()),

            Stat::make(__('This Month\'s Revenue'), 'MAD ' . Number::format($monthRevenue, 2))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('Total Revenue'), 'MAD ' . Number::format($totalRevenue, 2))
                ->description($totalOrders . ' ' . __('total orders'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make(__('Pending Orders'), (string) $pendingOrders)
                ->description(__('Awaiting processing'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 10 ? 'danger' : 'warning'),

            Stat::make(__('Avg. Order Value'), 'MAD ' . Number::format($avgOrderValue, 2))
                ->description(__('Per order average'))
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('gray'),

            Stat::make(__('Unique Customers'), (string) $totalCustomers)
                ->description(__('By phone number'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }

    /** @return array<int, int|float> */
    private function last7DaysRevenue(): array
    {
        $data = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->whereNotIn('delivery_status', ['cancelled'])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        return collect(range(6, 0))->map(function ($daysAgo) use ($data) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');

            return (float) ($data[$date] ?? 0);
        })->values()->all();
    }
}
