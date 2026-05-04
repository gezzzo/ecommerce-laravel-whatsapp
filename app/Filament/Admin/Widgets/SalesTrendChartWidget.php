<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesTrendChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '📈 ' . __('Daily Revenue Trend (Last 30 Days)');
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i));

        $revenue = Order::query()
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->whereNotIn('delivery_status', ['cancelled'])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $orderCounts = Order::query()
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->whereNotIn('delivery_status', ['cancelled'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->pluck('orders', 'date');

        return [
            'datasets' => [
                [
                    'label' => __('Revenue (MAD)'),
                    'data' => $days->map(fn (Carbon $d) => (float) ($revenue[$d->format('Y-m-d')] ?? 0))->values()->all(),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('Orders'),
                    'data' => $days->map(fn (Carbon $d) => (int) ($orderCounts[$d->format('Y-m-d')] ?? 0))->values()->all(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $days->map(fn (Carbon $d) => $d->format('d M'))->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => __('Revenue (MAD)')],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => ['display' => true, 'text' => __('Orders')],
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => true],
            ],
        ];
    }
}
