<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Models\Variant;
use App\Services\OrderWhatsappConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        $mode = StoreSetting::checkoutMode();

        $cart = $this->getCart();
        $cartItems = collect();
        $subtotal = 0;
        $itemCount = 0;

        if ($cart) {
            $cartItems = CartItem::with([
                'skuCode.skuable' => function ($morphTo) {
                    $morphTo->morphWith([
                        Product::class => ['category'],
                        Variant::class => ['product.category', 'size', 'color'],
                    ]);
                },
            ])->where('cart_id', $cart->id)->get()->map(function (CartItem $item) {
                $skuable = $item->skuCode?->skuable;
                if ($skuable instanceof Variant) {
                    $item->displayName  = $skuable->product->name;
                    $item->displayPrice = $skuable->selling_price;
                } elseif ($skuable instanceof Product) {
                    $item->displayName  = $skuable->name;
                    $item->displayPrice = $skuable->selling_price;
                }

                return $item;
            });

            $subtotal  = $cartItems->sum(fn (CartItem $item) => ($item->displayPrice ?? 0) * $item->quantity);
            $itemCount = $cartItems->sum('quantity');
        }

        // Load visible delivery zones
        $deliveryZones = DeliveryZone::where('visible', true)
            ->with('company')
            ->orderBy('city')
            ->get();

        $deliveryZonesJson = $deliveryZones->map(fn (DeliveryZone $zone) => [
            'id'           => $zone->id,
            'city'         => $zone->city,
            'delivery_fee' => (float) $zone->delivery_fee,
            'company'      => $zone->company?->name ?? '',
            'company_id'   => $zone->delivery_company_id,
        ])->toJson();

        // Shipping config for JS
        $shippingMode = StoreSetting::shippingMode();
        $shippingConfig = [
            'mode'               => $shippingMode,
            'free_threshold'     => (float) StoreSetting::get('free_shipping_threshold', 200),
            'free_item_count'    => (int) StoreSetting::get('free_shipping_item_count', 3),
            'current_item_count' => $itemCount,
        ];

        return view('checkout.index', compact(
            'mode',
            'cartItems',
            'subtotal',
            'itemCount',
            'deliveryZones',
            'deliveryZonesJson',
            'shippingConfig'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => User::normalizePhone((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'phone'            => ['required', 'string', 'max:20'],
            'address'          => ['required', 'string', 'max:255'],
            'delivery_zone_id' => ['required', 'integer', 'exists:delivery_zones,id'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'payment_method'   => ['nullable', 'string', 'in:cod'],
        ]);

        $cart = $this->getCart();

        if (! $cart) {
            return redirect()->route('cart')->with('error', 'السلة فارغة.');
        }

        $cartItems = CartItem::with([
            'skuCode.skuable' => function ($morphTo) {
                $morphTo->morphWith([
                    Product::class => [],
                    Variant::class => ['product'],
                ]);
            },
        ])->where('cart_id', $cart->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'السلة فارغة.');
        }

        $subtotal = $cartItems->sum(function (CartItem $item) {
            $skuable = $item->skuCode?->skuable;

            return ($skuable?->selling_price ?? 0) * $item->quantity;
        });

        $itemCount = $cartItems->sum('quantity');

        // Use the centralized shipping calculator
        $deliveryZone = DeliveryZone::findOrFail($validated['delivery_zone_id']);
        $shipping     = StoreSetting::calculateShipping($subtotal, $itemCount, (float) $deliveryZone->delivery_fee);
        $total        = $subtotal + $shipping;

        $order = DB::transaction(function () use ($validated, $cart, $cartItems, $deliveryZone, $subtotal, $shipping, $total): Order {
            $customer = $this->resolveCheckoutCustomer($validated);

            $order = Order::create([
                'user_id'             => $customer->id,
                'order_number'        => 'ORD-' . strtoupper(uniqid()),
                'name'                => $validated['name'],
                'phone'               => $validated['phone'],
                'address'             => $validated['address'],
                'city'                => $deliveryZone->city,
                'comment'             => $validated['notes'] ?? null,
                'payment_method'      => $validated['payment_method'] ?? 'cod',
                'payment_status'      => 'not_paid',
                'delivery_status'     => 'pending',
                'delivery_zone_id'    => $deliveryZone->id,
                'delivery_company_id' => $deliveryZone->delivery_company_id,
                'subtotal'            => $subtotal,
                'shipping'            => $shipping,
                'discount'            => 0,
                'total'               => $total,
            ]);

            foreach ($cartItems as $item) {
                $skuable = $item->skuCode?->skuable;
                OrderItem::create([
                    'order_id' => $order->id,
                    'sku_code' => $item->sku_code,
                    'price'    => $skuable?->selling_price ?? 0,
                    'quantity' => $item->quantity,
                ]);
            }

            CartItem::where('cart_id', $cart->id)->delete();

            return $order;
        });

        session()->forget(['cart_count', 'cart_id']);

        return redirect(URL::temporarySignedRoute(
            'checkout.confirmation',
            now()->addDay(),
            ['order' => $order],
        ));
    }

    public function confirmation(Order $order, OrderWhatsappConfirmationService $confirmationService): View
    {
        $order->loadMissing([
            'items.skuCode.skuable' => function ($morphTo): void {
                $morphTo->morphWith([
                    Product::class => [],
                    Variant::class => ['product', 'size', 'color'],
                ]);
            },
        ]);

        return view('checkout.confirmation', [
            'order' => $order,
            'whatsappUrl' => $confirmationService->confirmationUrl($order),
            'confirmationMessage' => $confirmationService->confirmationMessage($order),
        ]);
    }

    /**
     * @param  array{name: string, phone: string}  $validated
     */
    private function resolveCheckoutCustomer(array $validated): User
    {
        $authenticatedUser = auth()->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        $customer = User::firstOrCreate(
            ['phone' => $validated['phone']],
            [
                'name'     => $validated['name'],
                'password' => Hash::make(Str::password(32)),
                'is_guest' => true,
            ]
        );

        if (! $customer->wasRecentlyCreated && $customer->is_guest && $customer->name !== $validated['name']) {
            $customer->update([
                'name' => $validated['name'],
            ]);
        }

        return $customer;
    }

    private function getCart(): ?Cart
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionCartId = session('cart_id');
        if ($sessionCartId) {
            return Cart::find($sessionCartId);
        }

        return null;
    }
}
