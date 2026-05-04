@extends('layouts.app')

@section('title', ($pageTitle ?? $category->name ?? $searchQuery ?? 'جميع المنتجات') . ' - متجري')
@section('meta_description', 'تسوق ' . ($category->name ?? 'أفضل المنتجات') . ' من متجري. أسعار منافسة، شحن سريع، وإرجاع مجاني. اكتشف العروض والخصومات الحصرية.')
@section('meta_keywords', ($category->name ?? 'منتجات') . ', تسوق, عروض, خصومات, متجري')

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "الرئيسية",
            "item": "{{ route('home') }}"
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": "{{ $category->name ?? $pageTitle ?? 'المنتجات' }}",
            "item": "{{ url()->current() }}"
        }
    ]
}
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2" aria-label="مسار التصفح">
        <a href="{{ route('home') }}" class="hover:text-primary-600">الرئيسية</a>
        <span>/</span>
        <span class="text-gray-800">{{ $category->name ?? $pageTitle ?? (isset($searchQuery) ? 'نتائج البحث: ' . $searchQuery : 'جميع المنتجات') }}</span>
    </nav>

    {{-- Page Title --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-6">
        @if(isset($searchQuery))
            🔍 نتائج البحث عن: "{{ $searchQuery }}"
        @elseif(isset($pageTitle))
            🔥 {{ $pageTitle }}
        @elseif(isset($category))
            {{ $category->name }}
        @else
            جميع المنتجات
        @endif
    </h1>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ===== SIDEBAR / FILTERS ===== --}}
        <aside class="w-full lg:w-64 shrink-0" aria-label="فلترة المنتجات">
            <form method="GET" id="filterForm">
                {{-- Keep search query if present --}}
                @if(isset($searchQuery))
                <input type="hidden" name="q" value="{{ $searchQuery }}">
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-20">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center justify-between">
                        <span>🔧 التصفية</span>
                        <a href="{{ request()->url() }}" class="text-xs text-primary-600 font-normal hover:underline">إعادة ضبط</a>
                    </h3>

                    {{-- Price Range --}}
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 text-sm mb-3">💰 نطاق السعر</h4>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="من"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                   id="filter-min-price">
                            <span class="text-gray-400">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="إلى"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                   id="filter-max-price">
                        </div>
                    </div>

                    {{-- Categories --}}
                    @if(($sidebarCategories ?? collect())->isNotEmpty())
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 text-sm mb-3">📂 الفئات</h4>
                        <ul class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($sidebarCategories as $cat)
                            <li>
                                <label class="flex items-center justify-between cursor-pointer text-sm group">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                               {{ in_array($cat->id, request('categories', [])) ? 'checked' : '' }}
                                               class="accent-primary-600"
                                               id="filter-cat-{{ $cat->id }}">
                                        <span class="text-gray-700 group-hover:text-primary-600 transition-colors">{{ $cat->name }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400">({{ $cat->products_count ?? 0 }})</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <button type="submit"
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors"
                            id="filter-submit">
                        تطبيق الفلتر
                    </button>
                </div>
            </form>
        </aside>

        {{-- ===== PRODUCTS GRID ===== --}}
        <div class="flex-1">
            {{-- Toolbar --}}
            <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                <p class="text-sm text-gray-500">
                    عرض <strong class="text-gray-800">{{ $products->total() ?? 0 }}</strong> منتج
                </p>
                <div class="flex items-center gap-3">
                    <select name="sort" form="filterForm" onchange="document.getElementById('filterForm').submit()"
                            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white"
                            id="sort-select"
                            aria-label="ترتيب المنتجات">
                        <option value="">الترتيب الافتراضي</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>الأحدث أولاً</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>السعر: من الأقل</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>السعر: من الأعلى</option>
                    </select>
                </div>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($products ?? [] as $product)
                    @include('partials.product-card', ['product' => $product])
                @empty
                <div class="col-span-full text-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-medium text-lg mb-2">لا توجد منتجات مطابقة</p>
                    <p class="text-sm">جرّب تغيير الفلتر أو البحث بكلمات مختلفة</p>
                    <a href="{{ route('products') }}" class="inline-block mt-4 text-primary-600 hover:underline text-sm font-medium">عرض جميع المنتجات ←</a>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if(isset($products) && $products->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $products->withQueryString()->links('partials.pagination') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
