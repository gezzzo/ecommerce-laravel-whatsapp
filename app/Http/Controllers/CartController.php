<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\SkuCode;
use App\Models\Variant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cartItems = collect();
        $subtotal = 0;

        $cart = $this->getOrCreateCart();

        if ($cart) {
            $cartItems = CartItem::with([
                'skuCode.skuable' => function ($morphTo) {
                    $morphTo->morphWith([
                        Product::class => ['category'],
                        Variant::class => ['product.category', 'size', 'color'],
                    ]);
                },
            ])
                ->where('cart_id', $cart->id)
                ->get()
                ->map(function (CartItem $item) {
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
                    } elseif ($skuable instanceof Product) {
                        $item->displayName = $skuable->name;
                        $item->displayPrice = $skuable->selling_price;
                        $item->displayImage = $skuable->thumbnail;
                        $item->displaySlug = $skuable->slug;
                        $item->displayCategory = $skuable->category->name ?? '';
                        $item->variantLabel = null;
                    }

                    return $item;
                });

            $subtotal = $cartItems->sum(fn (CartItem $item) => ($item->displayPrice ?? 0) * $item->quantity);
        }

        $shipping = $subtotal >= 200 ? 0 : 30;
        $total = $subtotal + $shipping;

        return view('cart.index', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = $validated['quantity'] ?? 1;

        // If product has variants, variant_id is required
        $product = Product::findOrFail($validated['product_id']);

        if ($product->has_variants && empty($validated['variant_id'])) {
            return back()->with('error', 'يرجى اختيار اللون/المقاس أولاً.');
        }

        $cart = $this->getOrCreateCart();

        /** @var SkuCode|null $skuCode */
        $skuCode = null;

        if (! empty($validated['variant_id'])) {
            $skuCode = SkuCode::where('skuable_type', Variant::class)
                ->where('skuable_id', $validated['variant_id'])
                ->first();
        } else {
            $skuCode = SkuCode::where('skuable_type', Product::class)
                ->where('skuable_id', $validated['product_id'])
                ->first();
        }

        if (! $skuCode) {
            return back()->with('error', 'المنتج غير متوفر حالياً.');
        }

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('sku_code', $skuCode->id)
            ->first();

        if ($existingItem) {
            CartItem::where('cart_id', $cart->id)
                ->where('sku_code', $skuCode->id)
                ->update(['quantity' => $existingItem->quantity + $quantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'sku_code' => $skuCode->id,
                'quantity' => $quantity,
            ]);
        }

        session(['cart_count' => CartItem::where('cart_id', $cart->id)->sum('quantity')]);

        return back()->with('success', 'تمت الإضافة إلى السلة بنجاح!');
    }

    public function update(Request $request, int $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = $this->getOrCreateCart();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('sku_code', $itemId)
            ->firstOrFail();

        if ($validated['quantity'] <= 0) {
            CartItem::where('cart_id', $cart->id)
                ->where('sku_code', $itemId)
                ->delete();
        } else {
            CartItem::where('cart_id', $cart->id)
                ->where('sku_code', $itemId)
                ->update(['quantity' => $validated['quantity']]);
        }

        session(['cart_count' => CartItem::where('cart_id', $cart->id)->sum('quantity')]);

        return back()->with('success', 'تم تحديث السلة.');
    }

    public function remove(int $itemId): RedirectResponse
    {
        $cart = $this->getOrCreateCart();

        CartItem::where('cart_id', $cart->id)
            ->where('sku_code', $itemId)
            ->delete();

        session(['cart_count' => CartItem::where('cart_id', $cart->id)->sum('quantity')]);

        return back()->with('success', 'تم حذف المنتج من السلة.');
    }

    private function getOrCreateCart(): Cart
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionCartId = session('cart_id');

        if ($sessionCartId) {
            $cart = Cart::find($sessionCartId);

            if ($cart) {
                return $cart;
            }
        }

        $cart = Cart::create([]);
        session(['cart_id' => $cart->id]);

        return $cart;
    }
}
