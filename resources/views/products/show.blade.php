@extends('layouts.app')

@section('title', $product->name . ' - متجري')
@section('meta_description', Str::limit(strip_tags($product->description), 160))
@section('meta_keywords', $product->name . ', ' . ($product->category->name ?? '') . ', شراء, متجري')
@section('og_type', 'product')
@section('og_title', $product->name . ' - متجري')
@section('og_image', asset('storage/' . $product->thumbnail))

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Product",
    "name": "{{ $product->name }}",
    "description": "{{ Str::limit(strip_tags($product->description), 300) }}",
    "image": "{{ asset('storage/' . $product->thumbnail) }}",
    "sku": "{{ $product->skuCode?->sku_code ?? $product->slug }}",
    "brand": {
        "@@type": "Brand",
        "name": "{{ config('app.name', 'متجري') }}"
    },
    "category": "{{ $product->category->name ?? '' }}",
    "offers": {
        "@@type": "Offer",
        "priceCurrency": "MAD",
        "price": "{{ $product->selling_price }}",
        "availability": "https://schema.org/{{ $product->has_variants || ($product->inventory?->quantity ?? 0) > 0 ? 'InStock' : 'OutOfStock' }}",
        "url": "{{ url()->current() }}",
        "seller": {
            "@@type": "Organization",
            "name": "{{ config('app.name', 'متجري') }}"
        }
    }
}
</script>

{{-- BreadcrumbList --}}
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
            "name": "المنتجات",
            "item": "{{ route('products') }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ $product->name }}",
            "item": "{{ url()->current() }}"
        }
    ]
}
</script>
@endpush

@php
    $hasDiscount = $product->price_before_discount && $product->price_before_discount > $product->selling_price;

    // Gather unique colors and sizes from variants
    $availableColors = $product->has_variants
        ? $product->variants->pluck('color')->filter()->unique('id')->values()
        : collect();

    $availableSizes = $product->has_variants
        ? $product->variants->pluck('size')->filter()->unique('id')->values()
        : collect();

    // Build variants JSON for JavaScript
    $variantsJson = $product->has_variants
        ? $product->variants->map(function ($v) {
            return [
                'id'          => $v->id,
                'color_id'    => $v->color_id,
                'size_id'     => $v->size_id,
                'selling_price'         => $v->selling_price,
                'price_before_discount' => $v->price_before_discount,
                'image'       => $v->image ? asset('storage/' . $v->image) : null,
                'stock'       => $v->inventory?->quantity ?? 0,
                'sku'         => $v->skuCode?->sku_code ?? '',
            ];
        })->toJson()
        : '[]';

    // Build all colors JSON for dynamic swatch rendering
    $allColorsJson = $availableColors->map(function ($c) {
        return [
            'id'       => $c->id,
            'name'     => $c->name,
            'hex_code' => $c->hex_code ?? '#ccc',
        ];
    })->toJson();

    $stockQty = $product->has_variants ? null : ($product->inventory?->quantity ?? 0);
    $descriptionHtml = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $product->description ?? '');
@endphp

@push('styles')
<style>
    .product-description,
    .product-description * {
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .product-description p {
        margin-bottom: .5rem;
        white-space: normal;
    }

    .product-description .ql-align-center {
        text-align: center;
    }

    .product-description [style*="255, 255, 255"] {
        color: #4b5563 !important;
    }

    .product-description [style*="230, 0, 0"] {
        color: #dc2626 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2" aria-label="مسار التصفح">
        <a href="{{ route('home') }}" class="hover:text-primary-600">الرئيسية</a>
        <span>/</span>
        <a href="{{ route('products') }}" class="hover:text-primary-600">المنتجات</a>
        @if($product->category)
        <span>/</span>
        <a href="{{ route('category', $product->category->slug ?? '') }}" class="hover:text-primary-600">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-gray-800">{{ $product->name }}</span>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-100 p-6 md:p-8">
        <div class="flex flex-col md:flex-row gap-10 min-w-0">

            {{-- ===== IMAGE GALLERY ===== --}}
            <div class="w-full md:w-5/12 min-w-0">
                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden mb-3 relative">
                    <button type="button"
                            class="block w-full h-full cursor-zoom-in"
                            onclick="openImageZoom(document.getElementById('mainImage').src)"
                            aria-label="تكبير صورة {{ $product->name }}">
                        <img src="{{ asset('storage/' . $product->thumbnail) }}"
                             alt="{{ $product->name }}"
                             id="mainImage"
                             class="w-full h-full object-contain transition-opacity duration-300"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 400%22><rect fill=%22%23f9fafb%22 width=%22400%22 height=%22400%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-size=%2280%22>📦</text></svg>'">
                    </button>

                    {{-- Zoom indicator --}}
                    <div class="absolute bottom-3 left-3 bg-black/30 backdrop-blur text-white text-xs px-2 py-1 rounded-lg pointer-events-none">
                        🔍 اضغط للتكبير
                    </div>
                </div>

                {{-- Thumbnails --}}
                @if($product->images->count() > 0)
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button onclick="changeMainImage('{{ asset('storage/' . $product->thumbnail) }}', this)"
                            class="gallery-thumb active w-16 h-16 rounded-xl border-2 border-primary-400 overflow-hidden flex-shrink-0 transition-all">
                        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="" class="w-full h-full object-cover">
                    </button>
                    @foreach($product->images as $img)
                    <button onclick="changeMainImage('{{ asset('storage/' . $img->image) }}', this)"
                            class="gallery-thumb w-16 h-16 rounded-xl border-2 border-transparent hover:border-primary-300 overflow-hidden flex-shrink-0 transition-all">
                        <img src="{{ asset('storage/' . $img->image) }}" alt="" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ===== PRODUCT DETAILS ===== --}}
            <div class="flex-1 min-w-0">
                {{-- Category --}}
                <a href="{{ route('category', $product->category->slug ?? '') }}" class="text-sm text-primary-600 font-medium mb-2 inline-block hover:underline">
                    {{ $product->category->name ?? 'الفئة' }}
                </a>

                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-3">{{ $product->name }}</h1>

                {{-- SKU --}}
                @if($product->skuCode)
                <p class="text-xs text-gray-400 mb-3">
                    كود المنتج: <span id="skuDisplay">{{ $product->skuCode->sku_code }}</span>
                </p>
                @endif

                {{-- Stock Status --}}
                <div class="flex items-center gap-2 mb-4">
                    @if($product->has_variants)
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full" id="stockStatus">اختر الخيارات</span>
                    @elseif($stockQty > 0)
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full" id="stockStatus">متوفر في المخزون ({{ $stockQty }})</span>
                    @else
                    <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full" id="stockStatus">غير متوفر حالياً</span>
                    @endif
                </div>

                {{-- Price --}}
                <div class="flex items-baseline gap-3 mb-6" id="priceSection">
                    <span class="text-3xl font-extrabold text-primary-600" id="currentPrice">{{ number_format($product->selling_price) }} درهم</span>
                    @if($hasDiscount)
                    <span class="text-lg text-gray-400 line-through" id="oldPrice">{{ number_format($product->price_before_discount) }} درهم</span>
                    <span class="badge-sale text-sm" id="savedAmount">وفّر {{ number_format($product->price_before_discount - $product->selling_price) }} درهم</span>
                    @else
                    <span class="text-lg text-gray-400 line-through hidden" id="oldPrice"></span>
                    <span class="badge-sale text-sm hidden" id="savedAmount"></span>
                    @endif
                </div>

                {{-- ===== VARIANT SELECTORS (Cascading: Size → Color) ===== --}}
                @if($product->has_variants)

                    {{-- Step 1: Size Selector (always visible) --}}
                    @if($availableSizes->isNotEmpty())
                    <div class="mb-5">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">
                            <span class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 text-primary-700 text-xs rounded-full mr-1">1</span>
                            المقاس: <span id="selectedSizeName" class="text-primary-600 font-normal">اختر المقاس</span>
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($availableSizes as $size)
                            <button type="button"
                                    class="size-btn px-4 py-1.5 border border-gray-200 rounded-lg text-sm hover:border-primary-400 hover:text-primary-600 transition-colors"
                                    data-size-id="{{ $size->id }}"
                                    data-size-name="{{ $size->name }}"
                                    onclick="selectSize(this)"
                                    aria-label="المقاس: {{ $size->name }}">
                                {{ $size->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Step 2: Color Selector (hidden until size is selected) --}}
                    @if($availableColors->isNotEmpty())
                    <div class="mb-5 transition-all duration-300" id="colorSelectorWrapper" style="display: none; opacity: 0;">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">
                            <span class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 text-primary-700 text-xs rounded-full mr-1">2</span>
                            اللون: <span id="selectedColorName" class="text-primary-600 font-normal">اختر اللون</span>
                        </h4>
                        <div class="flex flex-wrap gap-3" id="colorSwatchesContainer">
                            {{-- Populated dynamically by JS based on selected size --}}
                        </div>
                        <p class="text-xs text-gray-400 mt-2" id="colorHint"></p>
                    </div>
                    @endif

                    {{-- If product only has colors (no sizes) --}}
                    @if($availableSizes->isEmpty() && $availableColors->isNotEmpty())
                    <div class="mb-5">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">
                            اللون: <span id="selectedColorName" class="text-primary-600 font-normal">اختر اللون</span>
                        </h4>
                        <div class="flex flex-wrap gap-3">
                            @foreach($availableColors as $color)
                            <button type="button"
                                    class="color-swatch w-9 h-9 rounded-full border-2 border-gray-200 relative"
                                    style="background-color: {{ $color->hex_code ?? '#ccc' }}"
                                    data-color-id="{{ $color->id }}"
                                    data-color-name="{{ $color->name }}"
                                    onclick="selectColor(this)"
                                    title="{{ $color->name }}"
                                    aria-label="اللون: {{ $color->name }}">
                                <span class="sr-only">{{ $color->name }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                @endif

                {{-- Description --}}
                <div class="product-description text-gray-600 leading-relaxed mb-6 text-sm max-w-full min-w-0 overflow-hidden">
                    {!! $descriptionHtml !!}
                </div>

                {{-- Add to Cart Form --}}
                <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm" class="flex items-center gap-3 mt-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" id="selectedVariantId" value="">

                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" onclick="changeQty(-1)"
                                class="w-10 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors" aria-label="تقليل الكمية">−</button>
                        <input type="number" name="quantity" id="qty" value="1" min="1"
                               max="{{ $product->has_variants ? 99 : max($stockQty, 1) }}"
                               class="w-12 text-center text-sm font-semibold focus:outline-none border-x border-gray-200">
                        <button type="button" onclick="changeQty(1)"
                                class="w-10 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors" aria-label="زيادة الكمية">+</button>
                    </div>

                    <button type="submit"
                            id="addToCartBtn"
                            {{ $product->has_variants ? 'disabled' : ((!$product->has_variants && $stockQty <= 0) ? 'disabled' : '') }}
                            class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span id="addToCartText">{{ $product->has_variants ? 'اختر اللون والمقاس' : 'أضف للسلة' }}</span>
                    </button>
                </form>

                {{-- Wishlist --}}
                <form action="{{ route('wishlist.add') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" id="selectedWishlistVariantId" value="">
                    <button type="submit"
                            class="w-full border border-gray-200 rounded-xl flex items-center justify-center gap-2 py-2.5 text-gray-500 hover:text-red-500 hover:border-red-200 transition-colors text-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        أضف للمفضلة
                    </button>
                </form>

                {{-- Guarantees --}}
                <div class="grid grid-cols-3 gap-3 mt-6 pt-6 border-t border-gray-100">
                    @foreach([['🚚','شحن سريع','لجميع المحافظات'],['🔄','إرجاع مجاني','خلال 30 يوم'],['🔒','دفع آمن','100% مضمون']] as $g)
                    <div class="text-center">
                        <div class="text-xl mb-1">{{ $g[0] }}</div>
                        <div class="text-xs font-medium text-gray-700">{{ $g[1] }}</div>
                        <div class="text-xs text-gray-400">{{ $g[2] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="imageZoomModal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 p-4"
         role="dialog"
         aria-modal="true"
         aria-label="صورة المنتج">
        <button type="button"
                class="absolute inset-0 cursor-zoom-out"
                onclick="closeImageZoom()"
                aria-label="إغلاق الصورة المكبرة"></button>
        <img id="zoomedImage"
             src=""
             alt="{{ $product->name }}"
             class="relative z-10 max-h-[90vh] max-w-[95vw] rounded-xl object-contain shadow-2xl">
        <button type="button"
                class="absolute left-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-gray-700 shadow hover:bg-white"
                onclick="closeImageZoom()"
                aria-label="إغلاق">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ===== RELATED PRODUCTS ===== --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <section class="mt-12" aria-label="منتجات ذات صلة">
        <h2 class="text-xl font-bold text-gray-900 mb-6">منتجات ذات صلة</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($relatedProducts as $related)
                @include('partials.product-card', ['product' => $related])
            @endforeach
        </div>
    </section>
    @endif
</div>

@push('scripts')
<script>
    const variants = {!! $variantsJson !!};
    const allColors = {!! $allColorsJson !!};
    const hasSizes = {{ $availableSizes->isNotEmpty() ? 'true' : 'false' }};
    let selectedColorId = null;
    let selectedSizeId = null;

    function selectSize(el) {
        document.querySelectorAll('.size-btn').forEach(s => {
            s.classList.remove('active', 'border-primary-500', 'bg-primary-50', 'text-primary-700');
        });
        el.classList.add('active', 'border-primary-500', 'bg-primary-50', 'text-primary-700');
        selectedSizeId = parseInt(el.dataset.sizeId);
        document.getElementById('selectedSizeName').textContent = el.dataset.sizeName;

        // Reset color selection
        selectedColorId = null;
        const colorNameEl = document.getElementById('selectedColorName');
        if (colorNameEl) {
            colorNameEl.textContent = 'اختر اللون';
        }

        // Show available colors for this size
        showColorsForSize(selectedSizeId);

        // Reset cart button until color is selected (if colors exist)
        const colorWrapper = document.getElementById('colorSelectorWrapper');
        if (colorWrapper) {
            document.getElementById('selectedVariantId').value = '';
            document.getElementById('selectedWishlistVariantId').value = '';
            document.getElementById('addToCartBtn').disabled = true;
            document.getElementById('addToCartText').textContent = 'اختر اللون';
        } else {
            // No colors — match variant directly by size only
            matchVariant();
        }
    }

    function showColorsForSize(sizeId) {
        const wrapper = document.getElementById('colorSelectorWrapper');
        const container = document.getElementById('colorSwatchesContainer');
        const hint = document.getElementById('colorHint');
        if (!wrapper || !container) return;

        // Find which color_ids are available for this size
        const colorIdsForSize = [...new Set(
            variants
                .filter(v => v.size_id === sizeId && v.color_id)
                .map(v => v.color_id)
        )];

        if (colorIdsForSize.length === 0) {
            wrapper.style.display = 'none';
            wrapper.style.opacity = '0';
            // No colors for this size — try matching by size only
            matchVariant();
            return;
        }

        // Build color swatches
        const colorsForSize = allColors.filter(c => colorIdsForSize.includes(c.id));
        container.innerHTML = '';

        colorsForSize.forEach(color => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'color-swatch w-9 h-9 rounded-full border-2 border-gray-200 relative hover:scale-110 transition-transform';
            btn.style.backgroundColor = color.hex_code;
            btn.dataset.colorId = color.id;
            btn.dataset.colorName = color.name;
            btn.title = color.name;
            btn.setAttribute('aria-label', 'اللون: ' + color.name);
            btn.onclick = function() { selectColor(this); };
            const sr = document.createElement('span');
            sr.className = 'sr-only';
            sr.textContent = color.name;
            btn.appendChild(sr);
            container.appendChild(btn);
        });

        if (hint) {
            hint.textContent = colorsForSize.length + ' لون متوفر لهذا المقاس';
        }

        // Show with animation
        wrapper.style.display = 'block';
        requestAnimationFrame(() => {
            wrapper.style.opacity = '1';
        });
    }

    function selectColor(el) {
        document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active', 'ring-2', 'ring-primary-500', 'ring-offset-2'));
        el.classList.add('active', 'ring-2', 'ring-primary-500', 'ring-offset-2');
        selectedColorId = parseInt(el.dataset.colorId);
        const colorNameEl = document.getElementById('selectedColorName');
        if (colorNameEl) {
            colorNameEl.textContent = el.dataset.colorName;
        }
        matchVariant();
    }

    function matchVariant() {
        const matched = variants.find(v => {
            const colorMatch = !selectedColorId || v.color_id === selectedColorId;
            const sizeMatch = !selectedSizeId || v.size_id === selectedSizeId;
            return colorMatch && sizeMatch;
        });

        if (matched) {
            document.getElementById('selectedVariantId').value = matched.id;
            document.getElementById('selectedWishlistVariantId').value = matched.id;
            document.getElementById('currentPrice').textContent = Number(matched.selling_price).toLocaleString('ar-MA') + ' درهم';

            const oldPriceEl = document.getElementById('oldPrice');
            const savedAmountEl = document.getElementById('savedAmount');

            if (parseFloat(matched.price_before_discount) > parseFloat(matched.selling_price)) {
                oldPriceEl.textContent = Number(matched.price_before_discount).toLocaleString('ar-MA') + ' درهم';
                savedAmountEl.textContent = 'وفّر ' + Number(matched.price_before_discount - matched.selling_price).toLocaleString('ar-MA') + ' درهم';
                oldPriceEl.classList.remove('hidden');
                savedAmountEl.classList.remove('hidden');
            } else {
                oldPriceEl.classList.add('hidden');
                savedAmountEl.classList.add('hidden');
            }

            if (matched.image) {
                document.getElementById('mainImage').src = matched.image;
            }

            const stockEl = document.getElementById('stockStatus');
            if (matched.stock > 0) {
                stockEl.className = 'bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full';
                stockEl.textContent = 'متوفر في المخزون (' + matched.stock + ')';
                document.getElementById('addToCartBtn').disabled = false;
                document.getElementById('addToCartText').textContent = 'أضف للسلة';
                document.getElementById('qty').max = matched.stock;
            } else {
                stockEl.className = 'bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full';
                stockEl.textContent = 'غير متوفر حالياً';
                document.getElementById('addToCartBtn').disabled = true;
                document.getElementById('addToCartText').textContent = 'غير متوفر';
            }

            const skuEl = document.getElementById('skuDisplay');
            if (skuEl && matched.sku) {
                skuEl.textContent = matched.sku;
            }
        } else {
            document.getElementById('selectedVariantId').value = '';
            document.getElementById('selectedWishlistVariantId').value = '';
            document.getElementById('addToCartBtn').disabled = true;
            document.getElementById('addToCartText').textContent = hasSizes ? 'اختر المقاس واللون' : 'اختر اللون';
        }
    }

    function changeQty(delta) {
        const inp = document.getElementById('qty');
        const val = parseInt(inp.value) + delta;
        inp.value = Math.max(1, Math.min(parseInt(inp.max), val));
    }

    function changeMainImage(src, el) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.gallery-thumb').forEach(t => {
            t.classList.remove('active');
            t.classList.remove('border-primary-400');
            t.classList.add('border-transparent');
        });
        el.classList.add('active');
        el.classList.remove('border-transparent');
        el.classList.add('border-primary-400');
    }

    function openImageZoom(src) {
        const modal = document.getElementById('imageZoomModal');
        const zoomedImage = document.getElementById('zoomedImage');

        if (!modal || !zoomedImage) {
            return;
        }

        zoomedImage.src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeImageZoom() {
        const modal = document.getElementById('imageZoomModal');

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeImageZoom();
        }
    });
</script>
@endpush
@endsection
