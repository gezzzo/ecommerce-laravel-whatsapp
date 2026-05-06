<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyCouponRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupons;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\RedirectResponse;

class CouponController extends Controller
{
    public function apply(ApplyCouponRequest $request): RedirectResponse
    {
        $subtotal = $this->cartSubtotal();

        if ($subtotal <= 0) {
            session()->forget('coupon_code');

            return back()->with('error', 'السلة فارغة.');
        }

        $coupon = Coupons::query()
            ->available()
            ->where('code', $request->code())
            ->first();

        if (! $coupon) {
            session()->forget('coupon_code');

            return back()
                ->withInput()
                ->with('error', 'كود الخصم غير صالح.');
        }

        session(['coupon_code' => $coupon->code]);

        return back()->with(
            'success',
            'تم تطبيق كود الخصم. قيمة الخصم '.number_format($coupon->discountFor($subtotal)).' درهم.'
        );
    }

    public function destroy(): RedirectResponse
    {
        session()->forget('coupon_code');

        return back()->with('success', 'تم حذف كود الخصم.');
    }

    private function cartSubtotal(): float
    {
        $cart = $this->currentCart();

        if (! $cart) {
            return 0.0;
        }

        return (float) CartItem::with([
            'skuCode.skuable' => function ($morphTo): void {
                $morphTo->morphWith([
                    Product::class => [],
                    Variant::class => ['product'],
                ]);
            },
        ])
            ->where('cart_id', $cart->id)
            ->get()
            ->sum(function (CartItem $item): float {
                $skuable = $item->skuCode?->skuable;

                return (float) ($skuable?->selling_price ?? 0) * $item->quantity;
            });
    }

    private function currentCart(): ?Cart
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionCartId = session('cart_id');

        if (! $sessionCartId) {
            return null;
        }

        return Cart::find($sessionCartId);
    }
}
