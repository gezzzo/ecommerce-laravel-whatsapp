@extends('layouts.app')

@section('title', 'متجري - تسوق أفضل المنتجات بأسعار منافسة')
@section('meta_description', 'متجري - متجرك الإلكتروني الأول في مصر. اكتشف أفضل المنتجات المميزة بأسعار منافسة. شحن سريع، إرجاع مجاني، ودفع آمن. عروض يومية وخصومات تصل إلى 70%.')
@section('meta_keywords', 'تسوق أونلاين, منتجات مميزة, عروض, خصومات, شحن مجاني, متجر إلكتروني, مصر')

@section('content')

{{-- ===== HERO SECTION ===== --}}
<section class="gradient-hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-20">
        <div class="flex flex-col md:flex-row items-center gap-10">
            <div class="flex-1 text-center md:text-right">
                <span class="inline-block bg-primary-100 text-primary-700 text-sm font-semibold px-4 py-1 rounded-full mb-4">🎉 عروض الموسم</span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4">
                    اكتشف أفضل<br>
                    <span class="text-primary-600">المنتجات المميزة</span>
                </h1>
                <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto md:mx-0">
                    تسوق بسهولة وأمان. شحن سريع لجميع أنحاء الجمهورية مع ضمان استرجاع 30 يوم.
                </p>
                <div class="flex gap-3 justify-center md:justify-start flex-wrap">
                    <a href="{{ route('products') }}" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-8 py-3 rounded-xl transition-colors" id="hero-shop-now">
                        تسوق الآن
                    </a>
                    <a href="{{ route('offers') }}" class="border border-primary-300 text-primary-700 font-semibold px-8 py-3 rounded-xl hover:bg-primary-50 transition-colors" id="hero-offers">
                        العروض الحصرية
                    </a>
                </div>
                <div class="flex gap-8 mt-10 justify-center md:justify-start text-center">
                    <div>
                        <div class="text-2xl font-bold text-gray-900">+5000</div>
                        <div class="text-sm text-gray-500">منتج متاح</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">+20K</div>
                        <div class="text-sm text-gray-500">عميل سعيد</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">24/7</div>
                        <div class="text-sm text-gray-500">دعم متواصل</div>
                    </div>
                </div>
            </div>
            <div class="flex-1 flex justify-center">
                <div class="relative w-72 h-72 md:w-96 md:h-96">
                    <img src="{{ asset('images/hero-shopping.png') }}" alt="تسوق أفضل المنتجات" class="w-full h-full object-cover rounded-3xl">
                    {{-- Badge --}}
                    <div class="absolute -top-3 -left-3 bg-white rounded-2xl shadow-lg p-3 text-center animate-fade-up">
                        <div class="text-xl font-bold text-primary-600">50%</div>
                        <div class="text-xs text-gray-500">خصم</div>
                    </div>
                    <div class="absolute -bottom-3 -right-3 bg-white rounded-2xl shadow-lg p-3 flex items-center gap-2 animate-fade-up">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">✓</div>
                        <div>
                            <div class="text-xs font-semibold text-gray-800">شحن مجاني</div>
                            <div class="text-xs text-gray-400">فوق 200 درهم</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== FEATURES BAR ===== --}}
@if(! empty($homeFeatures))
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($homeFeatures as $feature)
        <div class="flex items-center gap-3">
            <div class="text-2xl">{{ $feature['icon'] }}</div>
            <div>
                <div class="font-semibold text-gray-800 text-sm">{{ $feature['title'] }}</div>
                <div class="text-xs text-gray-500">{{ $feature['subtitle'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ===== CATEGORIES ===== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12" aria-label="الفئات">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">تسوق حسب الفئة</h2>
        <a href="{{ route('categories') }}" class="text-primary-600 text-sm font-medium hover:underline">عرض الكل ←</a>
    </div>
    <div class="flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory sm:grid sm:grid-cols-4 md:grid-cols-6 sm:overflow-visible sm:pb-0">
        @forelse($categories ?? [] as $category)
        <a href="{{ route('category', $category->slug) }}"
           class="min-w-[9.5rem] sm:min-w-0 flex flex-col items-center gap-3 p-3 sm:p-4 bg-white rounded-2xl border border-gray-100 hover:border-primary-200 hover:bg-primary-50 transition-colors card-hover text-center snap-start">
            @if($category->icon)
            <img src="{{ asset('storage/' . $category->icon) }}" alt="{{ $category->name }}" class="w-full aspect-square object-cover rounded-xl shadow-sm">
            @else
            <div class="w-full aspect-square bg-primary-100 rounded-xl flex items-center justify-center text-4xl shadow-sm">🛍️</div>
            @endif
            <span class="text-sm font-bold text-gray-800">{{ $category->name }}</span>
            <span class="text-xs text-gray-400">({{ $category->products_count }})</span>
        </a>
        @empty
        @foreach([['emoji' => '👗', 'name' => 'أزياء'], ['emoji' => '💻', 'name' => 'إلكترونيات'], ['emoji' => '🏠', 'name' => 'المنزل'], ['emoji' => '💄', 'name' => 'جمال'], ['emoji' => '📱', 'name' => 'هواتف'], ['emoji' => '🎮', 'name' => 'ألعاب']] as $ph)
        <a href="#" class="min-w-[9.5rem] sm:min-w-0 flex flex-col items-center gap-3 p-3 sm:p-4 bg-white rounded-2xl border border-gray-100 hover:border-primary-200 hover:bg-primary-50 transition-colors card-hover text-center snap-start">
            <div class="w-full aspect-square bg-primary-100 rounded-xl flex items-center justify-center text-4xl shadow-sm">{{ $ph['emoji'] }}</div>
            <span class="text-sm font-bold text-gray-800">{{ $ph['name'] }}</span>
        </a>
        @endforeach
        @endforelse
    </div>
</section>

{{-- ===== FEATURED PRODUCTS ===== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-12" aria-label="منتجات مميزة">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">منتجات مميزة</h2>
        <a href="{{ route('products') }}" class="text-primary-600 text-sm font-medium hover:underline">عرض الكل ←</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse($featuredProducts ?? [] as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
        @for($i = 1; $i <= 10; $i++)
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover">
            <div class="relative">
                <div class="aspect-square bg-gray-100 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="p-3">
                <div class="h-3 w-16 bg-gray-100 rounded mb-2"></div>
                <div class="h-4 w-full bg-gray-100 rounded mb-2"></div>
                <div class="h-4 w-20 bg-gray-100 rounded"></div>
            </div>
        </div>
        @endfor
        @endforelse
    </div>
</section>

{{-- ===== BANNER PROMO ===== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-12" aria-label="عرض خاص">
    <div class="bg-gradient-to-l from-primary-600 to-primary-800 rounded-3xl p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6 text-white overflow-hidden relative">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="relative z-10">
            @if(filled($promoBanner['badge'] ?? null))
            <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-medium mb-3 inline-block">{{ $promoBanner['badge'] }}</span>
            @endif
            <h2 class="text-3xl font-extrabold mb-2">{{ $promoBanner['title'] ?? '' }}</h2>
            <p class="text-primary-100 text-lg">{{ $promoBanner['subtitle'] ?? '' }}</p>
        </div>
        <a href="{{ route('offers') }}" class="relative z-10 bg-white text-primary-700 font-bold px-8 py-3 rounded-xl hover:bg-primary-50 transition-colors shrink-0" id="promo-banner-cta">
            اشترِ الآن
        </a>
    </div>
</section>

{{-- ===== NEW ARRIVALS ===== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-16" aria-label="وصل حديثاً">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">وصل حديثاً 🆕</h2>
        <a href="{{ route('products', ['sort' => 'newest']) }}" class="text-primary-600 text-sm font-medium hover:underline">عرض الكل ←</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse($newArrivals ?? [] as $product)
            @include('partials.product-card', ['product' => $product, 'isNew' => true])
        @empty
        @for($i = 1; $i <= 5; $i++)
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover">
            <div class="relative">
                <div class="aspect-square bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full">جديد</span>
            </div>
            <div class="p-3">
                <div class="h-3 w-16 bg-gray-100 rounded mb-2"></div>
                <div class="h-4 w-full bg-gray-100 rounded mb-2"></div>
                <div class="h-4 w-20 bg-gray-100 rounded"></div>
            </div>
        </div>
        @endfor
        @endforelse
    </div>
</section>

@endsection
