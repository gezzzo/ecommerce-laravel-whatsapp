<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\DeliveryStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Reactive;

class OrdersOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    #[Reactive]
    public ?array $filters = null;

    protected function getStats(): array
    {

        // Get filter values from dashboard
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        $allOrdersCount = (clone $query)->count();
        // New Orders - status = 'New Order'
        $newOrdersCount = (clone $query)->where('status', OrderStatusEnum::NEW_ORDER->value)->count();

        // Confirmed Orders - status = 'Confirmed'
        $confirmedOrdersCount = (clone $query)->where('status', OrderStatusEnum::CONFIRMED->value)->count();

        // Unconfirmed Orders - status != 'New Order' AND status != 'Confirmed'
        $unConfirmedOrdersCount = (clone $query)
            ->whereNotIn('status', [OrderStatusEnum::NEW_ORDER->value, OrderStatusEnum::CONFIRMED->value])
            ->count();

        // Distribution Orders - delivery_status = 'Mise en distribution'
        $distributionOrdersCount = (clone $query)
            ->where('delivery_status', DeliveryStatusEnum::MISE_EN_DISTRIBUTION->value)
            ->count();

        // Shipped Orders - delivery_status in ['Expédié', 'expédier par AMANA']
        $shippedOrdersCount = (clone $query)
            ->whereIn('delivery_status', [
                DeliveryStatusEnum::EXPEDIE->value,
                DeliveryStatusEnum::EXPEDIER_PAR_AMANA->value,
            ])
            ->count();

        // Delivered Orders - delivery_status = 'Livré'
        $deliveredOrdersCount = (clone $query)
            ->where('delivery_status', DeliveryStatusEnum::LIVRE->value)
            ->count();

        // Returned Orders - delivery_status in ['Retourné', 'En retour par AMANA']
        $returnedOrdersCount = (clone $query)
            ->whereIn('delivery_status', [
                DeliveryStatusEnum::RETOURNE->value,
                DeliveryStatusEnum::EN_RETOUR_PAR_AMANA->value,
            ])
            ->count();

        // Orders for Delivery - Confirmed orders without delivery_status
        $deliveryForOrdersCount = (clone $query)
            ->where('status', OrderStatusEnum::CONFIRMED->value)
            ->whereNull('delivery_status')
            ->count();

        // Total Revenue - sum of total for delivered and confirmed orders
        $totalRevenue = (clone $query)
            ->where('delivery_status', DeliveryStatusEnum::LIVRE->value)
            ->Where('status', OrderStatusEnum::CONFIRMED->value)
            ->sum('total');

        $orderUrl = OrderResource::getUrl('index');

        return [
            Stat::make(__('all_orders'), $allOrdersCount)
                ->description(__('total_order_value'))
                ->color('orange')
                ->url($orderUrl),

            Stat::make(__('new_orders'), $newOrdersCount)
                ->description(__('new_orders_desc'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info')
                ->url($orderUrl.'?tableFilters[status][value]='.urlencode(OrderStatusEnum::NEW_ORDER->value)),

            Stat::make(__('confirmed_orders'), $confirmedOrdersCount)
                ->description(__('confirmed_orders_desc'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url($orderUrl.'?tableFilters[status][value]='.urlencode(OrderStatusEnum::CONFIRMED->value)),

            Stat::make(__('unconfirmed_orders'), $unConfirmedOrdersCount)
                ->description(__('unconfirmed_orders_desc'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url($orderUrl.'?tableFilters[unconfirmed][isActive]=true'),

            Stat::make(__('distribution_orders'), $distributionOrdersCount)
                ->description(__('distribution_orders_desc'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning')
                ->url($orderUrl.'?tableFilters[delivery_status][value]='.urlencode(DeliveryStatusEnum::MISE_EN_DISTRIBUTION->value)),

            Stat::make(__('shipped_orders'), $shippedOrdersCount)
                ->description(__('shipped_orders_desc'))
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('primary')
                ->url($orderUrl.'?tableFilters[shipped][isActive]=true'),

            Stat::make(__('delivered_orders'), $deliveredOrdersCount)
                ->description(__('delivered_orders_desc'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url($orderUrl.'?tableFilters[delivery_status][value]='.urlencode(DeliveryStatusEnum::LIVRE->value)),

            Stat::make(__('returned_orders'), $returnedOrdersCount)
                ->description(__('returned_orders_desc'))
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger')
                ->url($orderUrl.'?tableFilters[returned][isActive]=true'),

            Stat::make(__('orders_for_delivery'), $deliveryForOrdersCount)
                ->description(__('orders_for_delivery_desc'))
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('warning')
                ->url($orderUrl.'?tableFilters[for_delivery][isActive]=true'),

            Stat::make(__('total_revenue'), number_format($totalRevenue, 2).' '.__('admin.currency_code'))
                ->description(__('total_revenue_desc'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
