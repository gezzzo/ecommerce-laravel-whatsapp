<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SkuCode;
use App\Models\Variant;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $wishlist = $this->getOrCreateWishlist();

        $wishlistItems = WishlistItem::with([
            'skuCode.skuable' => function ($morphTo) {
                $morphTo->morphWith([
                    Product::class => ['category', 'inventory'],
                    Variant::class => ['product.category', 'size', 'color', 'inventory'],
                ]);
            },
        ])
            ->where('wishlist_id', $wishlist->id)
            ->latest()
            ->get()
            ->map(fn (WishlistItem $item): ?WishlistItem => $this->decorateWishlistItem($item))
            ->filter()
            ->values();

        $this->syncWishlistSession($wishlist);

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:variants,id'],
        ]);

        $product = Product::with(['skuCode', 'variants.skuCode'])
            ->findOrFail($validated['product_id']);

        if ($product->has_variants && empty($validated['variant_id'])) {
            return back()->with('error', 'يرجى اختيار اللون/المقاس أولاً.');
        }

        $skuCode = $this->resolveSkuCode($product, $validated['variant_id'] ?? null);

        if (! $skuCode) {
            return back()->with('error', 'المنتج غير متوفر حالياً.');
        }

        $wishlist = $this->getOrCreateWishlist();

        $existingItem = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('sku_code', $skuCode->id)
            ->first();

        if ($existingItem) {
            $existingItem->delete();
            $this->syncWishlistSession($wishlist);

            return back()->with('success', 'تم حذف المنتج من المفضلة.');
        }

        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'sku_code' => $skuCode->id,
        ]);

        $this->syncWishlistSession($wishlist);

        return back()->with('success', 'تمت الإضافة إلى المفضلة.');
    }

    public function remove(SkuCode $skuCode): RedirectResponse
    {
        $wishlist = $this->getOrCreateWishlist();

        WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('sku_code', $skuCode->id)
            ->delete();

        $this->syncWishlistSession($wishlist);

        return back()->with('success', 'تم حذف المنتج من المفضلة.');
    }

    private function resolveSkuCode(Product $product, ?int $variantId): ?SkuCode
    {
        if ($variantId !== null) {
            $variant = $product->variants
                ->first(fn (Variant $variant): bool => $variant->id === $variantId);

            return $variant?->skuCode;
        }

        return $product->skuCode;
    }

    private function decorateWishlistItem(WishlistItem $item): ?WishlistItem
    {
        $skuable = $item->skuCode?->skuable;

        if ($skuable instanceof Variant) {
            $item->displayName = $skuable->product->name;
            $item->displayPrice = $skuable->selling_price;
            $item->displayImage = $skuable->image ?: $skuable->product->thumbnail;
            $item->displaySlug = $skuable->product->slug;
            $item->displayCategory = $skuable->product->category->name ?? '';
            $item->variantLabel = collect([
                $skuable->color?->name,
                $skuable->size?->name,
            ])->filter()->implode(' / ');
            $item->productId = $skuable->product_id;
            $item->variantId = $skuable->id;
            $item->isInStock = ($skuable->inventory?->quantity ?? 0) > 0;

            return $item;
        }

        if ($skuable instanceof Product) {
            $item->displayName = $skuable->name;
            $item->displayPrice = $skuable->selling_price;
            $item->displayImage = $skuable->thumbnail;
            $item->displaySlug = $skuable->slug;
            $item->displayCategory = $skuable->category->name ?? '';
            $item->variantLabel = null;
            $item->productId = $skuable->id;
            $item->variantId = null;
            $item->isInStock = ($skuable->inventory?->quantity ?? 0) > 0;

            return $item;
        }

        return null;
    }

    private function getOrCreateWishlist(): Wishlist
    {
        if (auth()->check()) {
            return Wishlist::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionWishlistId = session('wishlist_id');

        if ($sessionWishlistId) {
            $wishlist = Wishlist::find($sessionWishlistId);

            if ($wishlist) {
                return $wishlist;
            }
        }

        $sessionId = session()->getId();

        if ($sessionId) {
            $wishlist = Wishlist::where('session_id', $sessionId)->first();

            if ($wishlist) {
                session(['wishlist_id' => $wishlist->id]);

                return $wishlist;
            }
        }

        $wishlist = Wishlist::create(['session_id' => $sessionId]);
        session(['wishlist_id' => $wishlist->id]);

        return $wishlist;
    }

    private function syncWishlistSession(Wishlist $wishlist): void
    {
        $skuCodes = WishlistItem::where('wishlist_id', $wishlist->id)
            ->pluck('sku_code')
            ->map(fn ($skuCode): int => (int) $skuCode)
            ->all();

        session([
            'wishlist_count' => count($skuCodes),
            'wishlist_sku_codes' => $skuCodes,
        ]);
    }
}
