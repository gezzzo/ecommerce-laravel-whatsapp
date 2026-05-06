@extends('layouts.app')

@section('title', 'إتمام الطلب - Mohtachima')
@section('meta_description', 'أتمي طلبك بأمان. شحن سريع لجميع المدن المغربية.')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2" aria-label="مسار التصفح">
        <a href="{{ route('home') }}" class="hover:text-primary-600">الرئيسية</a>
        <span>/</span>
        <a href="{{ route('cart') }}" class="hover:text-primary-600">السلة</a>
        <span>/</span>
        <span class="text-gray-800">إتمام الطلب</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">🛍️ إتمام الطلب</h1>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ===== MAIN FORM ===== --}}
        <div class="flex-1">

            {{-- ===== MODE: OPTIONAL — Show Login/Guest choice ===== --}}
            @if($mode === 'optional' && !auth()->check())
            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6" x-data="{ tab: 'guest' }">
                <h2 class="font-bold text-gray-900 mb-4 text-lg">كيف تريدين المتابعة؟</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <button type="button" x-on:click="tab='guest'"
                            :class="tab==='guest' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600'"
                            class="border-2 rounded-xl p-4 text-right transition-all" id="checkout-guest-tab">
                        <div class="text-2xl mb-2">📦</div>
                        <div class="font-semibold">متابعة كضيفة</div>
                        <div class="text-xs mt-1 opacity-70">أدخلي بيانات الشحن فقط</div>
                    </button>

                    <button type="button" x-on:click="tab='login'"
                            :class="tab==='login' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600'"
                            class="border-2 rounded-xl p-4 text-right transition-all" id="checkout-login-tab">
                        <div class="text-2xl mb-2">👤</div>
                        <div class="font-semibold">تسجيل الدخول</div>
                        <div class="text-xs mt-1 opacity-70">للوصول لطلباتك لاحقاً</div>
                    </button>

                    <div x-show="tab==='login'" x-cloak class="md:col-span-2 mt-2">
                        <form action="{{ route('login.post') }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ route('checkout') }}">
                            <input type="tel" name="phone" placeholder="رقم الهاتف"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400" required>
                            <input type="password" name="password" placeholder="كلمة المرور"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400" required>
                            <button type="submit"
                                    class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                                تسجيل الدخول والمتابعة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- ===== SHIPPING FORM ===== --}}
            <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
                @csrf

                {{-- Shipping Info --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                    <h2 class="font-bold text-gray-900 mb-5 text-lg flex items-center gap-2">
                        🚚 بيانات الشحن
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل *</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}"
                                   placeholder="الاسم الكامل"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 @error('name') border-red-400 @enderror"
                                   required id="checkout-name">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف *</label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"
                                   placeholder="06xxxxxxxx"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 @error('phone') border-red-400 @enderror"
                                   required id="checkout-phone">
                            @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Delivery Zone (City) Selector --}}
                        <div class="md:col-span-2" x-data="deliveryZoneSelector()" x-init="init()" x-on:click.outside="open = false">
                            <label class="block text-sm font-medium text-gray-700 mb-1">المدينة (منطقة التوصيل) *</label>
                            <div class="relative">
                                <input type="text"
                                       x-model="search"
                                       x-on:focus="open = true"
                                       x-on:input="open = true"
                                       placeholder="ابحثي عن مدينتك..."
                                       autocomplete="off"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 @error('delivery_zone_id') border-red-400 @enderror"
                                       id="checkout-city-search">

                                <input type="hidden" name="delivery_zone_id" x-model="selectedZoneId" id="delivery_zone_id">

                                {{-- Dropdown --}}
                                <div x-show="open && filteredZones.length > 0"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                    <template x-for="zone in filteredZones" :key="zone.id">
                                        <button type="button"
                                                x-on:click="selectZone(zone)"
                                                class="w-full text-right px-4 py-2.5 hover:bg-primary-50 transition-colors text-sm border-b border-gray-50 last:border-0">
                                            <span x-text="zone.city" class="font-medium text-gray-800"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- No results --}}
                                <div x-show="open && search.length > 0 && filteredZones.length === 0"
                                     x-cloak
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl px-4 py-3 text-sm text-gray-400 text-center">
                                    لا توجد نتائج لـ "<span x-text="search"></span>"
                                </div>

                                {{-- Selected zone badge --}}
                                <div x-show="selectedZoneName" x-cloak class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs px-3 py-1 rounded-full">
                                        📍 <span x-text="selectedZoneName"></span>
                                    </span>
                                    <button type="button" x-on:click="clearZone()" class="text-xs text-gray-400 hover:text-red-500">✕ تغيير</button>
                                </div>
                            </div>
                            @error('delivery_zone_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">العنوان الكامل *</label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                   placeholder="الشارع، الحي، الرقم..."
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 @error('address') border-red-400 @enderror"
                                   required id="checkout-address">
                            @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات إضافية</label>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                   placeholder="أي تعليمات للتوصيل..."
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                   id="checkout-notes">
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                    <h2 class="font-bold text-gray-900 mb-4 text-lg">💳 طريقة الدفع</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-primary-300 transition-colors">
                            <input type="radio" name="payment_method" value="cod" checked class="accent-primary-600">
                            <div>
                                <div class="font-medium text-sm">الدفع عند الاستلام</div>
                                <div class="text-xs text-gray-400">ادفعي نقداً لدى التسليم</div>
                            </div>
                            <span class="mr-auto text-2xl">💵</span>
                        </label>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-colors text-lg"
                        id="place-order-btn">
                    ✅ تأكيد الطلب
                </button>
            </form>
        </div>

        {{-- ===== ORDER SUMMARY ===== --}}
        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-20">
                <h3 class="font-bold text-gray-900 mb-4">📋 ملخص الطلب</h3>

                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                    @forelse($cartItems as $item)
                    <div class="flex items-center gap-3 text-sm">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $item->displayName ?? 'منتج' }}</p>
                            <p class="text-gray-400 text-xs">{{ $item->quantity }} × {{ number_format($item->displayPrice ?? 0) }} درهم</p>
                        </div>
                        <span class="font-semibold text-primary-600 shrink-0">{{ number_format(($item->displayPrice ?? 0) * $item->quantity) }} درهم</span>
                    </div>
                    @empty
                    <p class="text-gray-400 text-sm text-center py-4">السلة فارغة</p>
                    @endforelse
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">المجموع الفرعي</span>
                        <span>{{ number_format($subtotal) }} درهم</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">الشحن</span>
                        <span id="shipping-display" class="text-gray-500">
                            @if($shippingConfig['mode'] === 'free')
                                <span class="text-green-600 font-medium">✅ مجاني</span>
                            @else
                                اختاري المدينة أولاً
                            @endif
                        </span>
                    </div>
                    <div class="text-xs text-green-600" id="free-shipping-hint">
                        @if($shippingConfig['mode'] === 'free')
                            🎁 شحن مجاني على جميع الطلبات
                        @elseif($shippingConfig['mode'] === 'free_after_amount')
                            🎁 شحن مجاني للطلبات فوق {{ number_format($shippingConfig['free_threshold']) }} درهم
                        @elseif($shippingConfig['mode'] === 'free_after_items')
                            🎁 شحن مجاني عند شراء {{ $shippingConfig['free_item_count'] }} منتجات أو أكثر
                        @endif
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t border-gray-100 pt-2 mt-2">
                        <span>الإجمالي</span>
                        <span class="text-primary-600" id="total-display">{{ number_format($subtotal) }} درهم</span>
                    </div>
                </div>

                <a href="{{ route('cart') }}" class="mt-4 block text-center text-sm text-gray-400 hover:text-primary-600">
                    ← تعديل السلة
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Alpine.js + delivery zone logic --}}
<script>
    // Define the component BEFORE Alpine loads
    const allDeliveryZones = {!! $deliveryZonesJson !!};
    const subtotalAmount = {{ $subtotal }};
    const shippingConfig = {!! json_encode($shippingConfig) !!};

    function calculateShipping(zoneFee) {
        switch (shippingConfig.mode) {
            case 'free':
                return 0;
            case 'paid':
                return zoneFee;
            case 'free_after_amount':
                return subtotalAmount >= shippingConfig.free_threshold ? 0 : zoneFee;
            case 'free_after_items':
                return shippingConfig.current_item_count >= shippingConfig.free_item_count ? 0 : zoneFee;
            default:
                return zoneFee;
        }
    }

    function deliveryZoneSelector() {
        return {
            search: '',
            open: false,
            selectedZoneId: '',
            selectedZoneName: '',
            selectedZoneFee: 0,

            init() {},

            get filteredZones() {
                if (!this.search || this.search.length === 0) {
                    return allDeliveryZones.slice(0, 20);
                }
                const q = this.search.toLowerCase();
                return allDeliveryZones.filter(z => z.city.toLowerCase().includes(q)).slice(0, 20);
            },

            selectZone(zone) {
                this.selectedZoneId = zone.id;
                this.selectedZoneName = zone.city;
                this.selectedZoneFee = zone.delivery_fee;
                this.search = zone.city;
                this.open = false;

                this.updateSummary(zone.delivery_fee);
            },

            clearZone() {
                this.selectedZoneId = '';
                this.selectedZoneName = '';
                this.selectedZoneFee = 0;
                this.search = '';

                const shippingEl = document.getElementById('shipping-display');
                if (shippingConfig.mode === 'free') {
                    shippingEl.innerHTML = '<span class="text-green-600 font-medium">✅ مجاني</span>';
                } else {
                    shippingEl.textContent = 'اختاري المدينة أولاً';
                }
                document.getElementById('total-display').textContent = Number(subtotalAmount).toLocaleString('ar-MA') + ' درهم';
            },

            updateSummary(zoneFee) {
                const shippingEl = document.getElementById('shipping-display');
                const totalEl = document.getElementById('total-display');

                let shipping = calculateShipping(zoneFee);
                let total = subtotalAmount + shipping;

                if (shipping === 0) {
                    shippingEl.innerHTML = '<span class="text-green-600 font-medium">✅ مجاني</span>';
                } else {
                    shippingEl.textContent = Number(shipping).toLocaleString('ar-MA') + ' درهم';
                    shippingEl.className = '';
                }

                totalEl.textContent = Number(total).toLocaleString('ar-MA') + ' درهم';
            }
        };
    }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
