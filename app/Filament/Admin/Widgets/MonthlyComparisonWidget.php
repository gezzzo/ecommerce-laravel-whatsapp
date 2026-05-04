<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class MonthlyComparisonWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $thisMonth = $this->getMonthStats(now()->startOfMonth(), now());
        $lastMonth = $this->getMonthStats(
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );

        $revenueChange = $lastMonth['revenue'] > 0
            ? round((($thisMonth['revenue'] - $lastMonth['revenue']) / $lastMonth['revenue']) * 100, 1)
            : ($thisMonth['revenue'] > 0 ? 100 : 0);

        $ordersChange = $lastMonth['orders'] > 0
            ? round((($thisMonth['orders'] - $lastMonth['orders']) / $lastMonth['orders']) * 100, 1)
            : ($thisMonth['orders'] > 0 ? 100 : 0);

        $repeatCustomers = Order::query()
            ->whereNotNull('phone')
            ->whereNotIn('delivery_status', ['cancelled'])
            ->selectRaw('phone, COUNT(*) as cnt')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $totalCustomers = Order::query()
            ->whereNotNull('phone')
            ->distinct('phone')
            ->count('phone');

        $repeatRate = $totalCustomers > 0
            ? round(($repeatCustomers / $totalCustomers) * 100, 1)
            : 0;

        $cancelRate = Order::count() > 0
            ? round((Order::where('delivery_status', 'cancelled')->count() / Order::count()) * 100, 1)
            : 0;

        return [
            Stat::make(__('This Month Revenue'), 'MAD ' . Number::format($thisMonth['revenue'], 2))
                ->description($this->formatChange($revenueChange) . ' ' . __('vs last month'))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([$lastMonth['revenue'], $thisMonth['revenue']]),

            Stat::make(__('This Month Orders'), (string) $thisMonth['orders'])
                ->description($this->formatChange($ordersChange) . ' ' . __('vs last month'))
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger')
                ->chart([$lastMonth['orders'], $thisMonth['orders']]),

            Stat::make(__('Avg Order Value'), 'MAD ' . Number::format($thisMonth['avg'], 2))
                ->description(__('Last month') . ': MAD ' . Number::format($lastMonth['avg'], 2))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make(__('Repeat Customer Rate'), $repeatRate . '%')
                ->description("{$repeatCustomers} " . __('out of') . " {$totalCustomers} " . __('customers'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($repeatRate >= 20 ? 'success' : 'warning'),

            Stat::make(__('Cancellation Rate'), $cancelRate . '%')
                ->description(Order::where('delivery_status', 'cancelled')->count() . ' ' . __('cancelled orders'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($cancelRate <= 5 ? 'success' : 'danger'),

            Stat::make(__('Conversion (Delivered)'), $this->getDeliveryRate() . '%')
                ->description(__('Orders successfully delivered'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($this->getDeliveryRate() >= 70 ? 'success' : 'warning'),
        ];
    }

    /**
     * @return array{revenue: float, orders: int, avg: float}
     */
    private function getMonthStats(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $query = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('delivery_status', ['cancelled']);

        $revenue = (float) $query->sum('total');
        $orders = (int) $query->count();
        $avg = $orders > 0 ? round($revenue / $orders, 2) : 0;

        return compact('revenue', 'orders', 'avg');
    }

    private function formatChange(float $change): string
    {
        $prefix = $change >= 0 ? '+' : '';

        return $prefix . $change . '%';
    }

    private function getDeliveryRate(): float
    {
        $total = Order::count();
        if ($total === 0) {
            return 0;
        }

        $delivered = Order::where('delivery_status', 'delivered')->count();

        return round(($delivered / $total) * 100, 1);
    }
}
