<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class PaymentMethodChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'half';

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '💳 ' . __('Payment Method Distribution');
    }

    protected function getData(): array
    {
        $methods = Order::query()
            ->selectRaw('payment_method, COUNT(*) as count')
            ->whereNotIn('delivery_status', ['cancelled'])
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->pluck('count', 'payment_method');

        $colors = [
            'cod' => '#f59e0b',
            'card' => '#6366f1',
            'bank_transfer' => '#10b981',
            'wallet' => '#ec4899',
            'online' => '#3b82f6',
        ];

        return [
            'datasets' => [
                [
                    'data' => $methods->values()->all(),
                    'backgroundColor' => $methods->keys()->map(fn ($key) => $colors[$key] ?? '#9ca3af')->values()->all(),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $methods->keys()->map(fn ($key) => match ($key) {
                'cod' => '💵 ' . __('Cash on Delivery'),
                'card' => '💳 ' . __('Credit Card'),
                'bank_transfer' => '🏦 ' . __('Bank Transfer'),
                'wallet' => '📱 ' . __('Wallet'),
                'online' => '🌐 ' . __('Online Payment'),
                default => ucfirst(str_replace('_', ' ', $key)),
            })->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
