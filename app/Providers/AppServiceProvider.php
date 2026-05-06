<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\Wishlist;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        View::composer(['layouts.app', 'pages.contact'], function ($view) {
            $view->with('storeName', StoreSetting::storeName());
            $view->with('contactInfo', StoreSetting::contactInfo());
        });

        View::composer('layouts.app', function ($view) {
            $wishlistSkuCodes = $this->currentWishlistSkuCodes();

            $view->with('navCategories', Category::orderBy('sort_order')->limit(8)->get());
            $view->with('wishlistCount', count($wishlistSkuCodes));
            $view->with('announcementBarText', StoreSetting::announcementBarText());
            $view->with('metaPixelId', StoreSetting::metaPixelId());
        });

        View::composer('home', function ($view) {
            $view->with('homeFeatures', StoreSetting::homeFeatures());
            $view->with('promoBanner', StoreSetting::promoBanner());
        });

        View::composer('partials.product-card', function ($view) {
            $view->with('wishlistSkuCodes', $this->currentWishlistSkuCodes());
        });
    }

    /**
     * @return array<int, int>
     */
    private function currentWishlistSkuCodes(): array
    {
        if (request()->attributes->has('wishlist_sku_codes')) {
            return request()->attributes->get('wishlist_sku_codes');
        }

        $wishlist = null;

        if (auth()->check()) {
            $wishlist = Wishlist::where('user_id', auth()->id())->first();
        } elseif (session('wishlist_id')) {
            $wishlist = Wishlist::find(session('wishlist_id'));
        } elseif (session()->getId()) {
            $wishlist = Wishlist::where('session_id', session()->getId())->first();
        }

        $wishlistSkuCodes = $wishlist
            ? $wishlist->items()->pluck('sku_code')->map(fn ($skuCode): int => (int) $skuCode)->all()
            : [];

        session([
            'wishlist_count' => count($wishlistSkuCodes),
            'wishlist_sku_codes' => $wishlistSkuCodes,
        ]);

        request()->attributes->set('wishlist_sku_codes', $wishlistSkuCodes);

        return $wishlistSkuCodes;
    }
}
