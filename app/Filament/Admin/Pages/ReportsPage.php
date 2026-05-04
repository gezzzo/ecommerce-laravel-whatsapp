<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\CategoryPerformanceWidget;
use App\Filament\Admin\Widgets\CouponPerformanceWidget;
use App\Filament\Admin\Widgets\MonthlyComparisonWidget;
use App\Filament\Admin\Widgets\PaymentMethodChartWidget;
use App\Filament\Admin\Widgets\SalesTrendChartWidget;
use BackedEnum;
use Filament\Pages\Page;

class ReportsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $slug = 'reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    public static function getNavigationLabel(): string
    {
        return __('Reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Analytics');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Sales Reports & Analytics');
    }

    public function getWidgets(): array
    {
        return [
            MonthlyComparisonWidget::class,
            SalesTrendChartWidget::class,
            CategoryPerformanceWidget::class,
            CouponPerformanceWidget::class,
            PaymentMethodChartWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
