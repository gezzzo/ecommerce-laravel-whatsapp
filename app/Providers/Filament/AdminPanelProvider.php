<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocale;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->brandName(__('Mohtachima'))
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make(fn (): string => __('Catalog')),
                NavigationGroup::make(fn (): string => __('Orders')),
                NavigationGroup::make(fn (): string => __('Delivery')),
                NavigationGroup::make(fn (): string => __('Users')),
                NavigationGroup::make(fn (): string => __('WhatsApp')),
                NavigationGroup::make(fn (): string => __('Analytics')),
                NavigationGroup::make(fn (): string => __('Settings')),
                NavigationGroup::make(fn (): string => __('Administration')),

            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'ar' ? 'English' : 'عربي')
                    ->url(fn () => route('switch-language'))
                    ->icon('heroicon-o-language'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup(fn (): string => __('Administration'))
                    ->navigationSort(2),

            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::body.start',
            fn (): string => Blade::render('
                @if(app()->getLocale() === "ar")
                    <script>
                        document.documentElement.dir = "rtl";
                        document.documentElement.lang = "ar";
                        document.body.classList.add("rtl-layout");
                    </script>
                    <style>
                        :root { direction: rtl; }
                        html { direction: rtl; font-family: "Cairo", "Segoe UI", sans-serif; }
                        body { direction: rtl; font-family: "Cairo", "Segoe UI", sans-serif; }
                        .fi-body { direction: rtl; font-family: "Cairo", "Segoe UI", sans-serif; }
                        [dir="rtl"] .fi-sidebar { text-align: right; }
                        [dir="rtl"] .fi-main { text-align: right; }
                        [dir="rtl"] .fi-topbar { text-align: right; }
                        [dir="rtl"] .fi-header { text-align: right; }
                        [dir="rtl"] .fi-content { text-align: right; }
                        [dir="rtl"] input, [dir="rtl"] select, [dir="rtl"] textarea { text-align: right; }
                        [dir="rtl"] .fi-ta-table th, [dir="rtl"] .fi-ta-table td { text-align: right; }
                        [dir="rtl"] .fi-modal-content { text-align: right; }
                    </style>
                @else
                    <script>
                        document.documentElement.dir = "ltr";
                        document.documentElement.lang = "en";
                        document.body.classList.remove("rtl-layout");
                    </script>
                @endif
            ')
        );

        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => '<link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">'
        );
    }
}
