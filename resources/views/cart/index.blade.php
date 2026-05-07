@extends('layouts.app')

@section('title', 'سلة التسوق - Mohtachima')
@section('meta_description', 'سلة التسوق - راجعي منتجاتك وأتمي عملية الشراء بأمان. شحن سريع لجميع المدن.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2" aria-label="مسار التصفح">
        <a href="{{ route('home') }}" class="hover:text-primary-600">الرئيسية</a>
        <span>/</span>
        <span class="text-gray-800">سلة التسوق</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">🛒 سلة التسوق</h1>

    @if($cartItems->isNotEmpty())
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Cart Items --}}
        <div class="flex-1 space-y-4">
            @foreach($cartItems as $item)
            <article class="bg-white rounded-2xl border border-gray-100 p-4 flex items-start gap-4 animate-fade-up">
                {{-- Image --}}
                <a href="{{ route('product', $item->displaySlug ?? '') }}" class="shrink-0">
                    <img src="{{ asset('storage/' . ($item->displayImage ?? '')) }}"
                         alt="{{ $item->displayName ?? '' }}"
                         loading="lazy"
                         class="w-20 h-20 object-cover rounded-xl"
                         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 80 80%22><rect fill=%22%23f3f4f6%22 width=%2280%22 height=%2280%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-size=%2230%22>📦</text></svg>'">
                </a>

                <div class="flex-1 min-w-0">
                    {{-- Product Name --}}
                    <a href="{{ route('product', $item->displaySlug ?? '') }}"
                       class="font-semibold text-gray-800 hover:text-primary-600 line-clamp-2 text-sm transition-colors">
                        {{ $item->displayName ?? 'منتج' }}
                    </a>

                    {{-- Category --}}
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->displayCategory ?? '' }}</p>

                    {{-- Variant info (color, size) --}}
                    @if($item->variantLabel)
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $item->variantLabel }}</span>
                    </div>
                    @endif

                    {{-- Price & Quantity Controls --}}
                    <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                            <form action="{{ route('cart.update', $item->sku_code) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                <button type="submit"
                                        class="w-8 h-8 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                        aria-label="تقليل الكمية">−</button>
                            </form>
                            <span class="w-8 text-center text-sm font-semibold">{{ $item->quantity }}</span>
                            <form action="{{ route('cart.update', $item->sku_code) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                <button type="submit"
                                        class="w-8 h-8 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                        aria-label="زيادة الكمية">+</button>
                            </form>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-left">
                                <span class="font-bold text-primary-600">{{ number_format(($item->displayPrice ?? 0) * $item->quantity) }} درهم</span>
                                @if($item->quantity > 1)
                                <div class="text-xs text-gray-400">{{ number_format($item->displayPrice ?? 0) }} درهم × {{ $item->quantity }}</div>
                                @endif
                            </div>
                            <form action="{{ route('cart.remove', $item->sku_code) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                        aria-label="حذف {{ $item->displayName ?? '' }} من السلة">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Order Summary --}}
        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-20">
                <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    📋 ملخص الطلب
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">المجموع الفرعي ({{ $cartItems->sum('quantity') }} منتج)</span>
                        <span>{{ number_format($subtotal) }} درهم</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">الشحن</span>
                        <span class="{{ $shipping == 0 ? 'text-green-600 font-medium' : '' }}">
                            {{ $shipping == 0 ? '✅ مجاني' : number_format($shipping) . ' درهم' }}
                        </span>
                    </div>
                    @if($discount > 0 && $appliedCoupon)
                    <div class="flex justify-between text-green-600 font-medium">
                        <span>الخصم ({{ $appliedCoupon->code }})</span>
                        <span>-{{ number_format($discount) }} درهم</span>
                    </div>
                    @endif

                    <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-lg">
                        <span>الإجمالي</span>
                        <span class="text-primary-600">{{ number_format($total) }} درهم</span>
                    </div>
                </div>

                {{-- Coupon --}}
                <form action="{{ route('coupon.apply') }}" method="POST" class="mt-4">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="coupon" placeholder="كود الخصم"
                               value="{{ old('coupon', $appliedCoupon?->code) }}"
                               class="flex-1 border @error('coupon') border-red-400 @else border-gray-200 @enderror rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                               id="coupon-input"
                               aria-label="كود الخصم">
                        <button type="submit"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-colors"
                                id="coupon-apply">
                            تطبيق
                        </button>
                    </div>
                    @error('coupon')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </form>
                @if($appliedCoupon)
                <form action="{{ route('coupon.destroy') }}" method="POST" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                        حذف كود الخصم
                    </button>
                </form>
                @endif

                <a href="{{ route('checkout') }}"
                   class="mt-4 w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm"
                   id="checkout-btn">
                    متابعة الدفع ←
                </a>
                <a href="{{ route('products') }}" class="mt-2 w-full text-center text-sm text-gray-500 hover:text-primary-600 block py-2">
                    ← متابعة التسوق
                </a>
            </div>
        </div>
    </div>

    @else
    {{-- Empty Cart --}}
    <div class="text-center py-20 animate-fade-up">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">سلتك فارغة!</h2>
        <p class="text-gray-500 mb-6">لم تضيفي أي منتجات بعد. استعرضي منتجاتنا وأضيفي ما يعجبك.</p>
        <a href="{{ route('products') }}"
           class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-semibold px-8 py-3 rounded-xl transition-colors"
           id="empty-cart-shop">
            تسوقي الآن
        </a>
    </div>
    @endif
</div>
@endsection
