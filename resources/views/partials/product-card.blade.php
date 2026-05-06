{{-- resources/views/partials/product-card.blade.php --}}
@php
    $hasDiscount = $product->price_before_discount && $product->price_before_discount > $product->selling_price;
    $discountPercent = $hasDiscount ? round((($product->price_before_discount - $product->selling_price) / $product->price_before_discount) * 100) : 0;
    $stockQty = $product->inventory?->quantity ?? 0;
    $isInStock = $product->has_variants ? true : $stockQty > 0;
    $variantsCount = $product->relationLoaded('variants') ? $product->variants->count() : ($product->variants_count ?? 0);
    $wishlistSkuCodes = collect($wishlistSkuCodes ?? session('wishlist_sku_codes', []))->map(fn ($skuCode) => (int) $skuCode);
    $primaryWishlistVariant = $product->has_variants && $product->relationLoaded('variants')
        ? $product->variants->sortBy('id')->first()
        : null;
    $primaryWishlistSkuCode = $product->has_variants
        ? $primaryWishlistVariant?->skuCode?->id
        : ($product->relationLoaded('skuCode') ? $product->skuCode?->id : null);
    $isWishlisted = $primaryWishlistSkuCode && $wishlistSkuCodes->contains((int) $primaryWishlistSkuCode);
@endphp

<article class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover group" itemscope itemtype="https://schema.org/Product">
    <div class="relative">
        <a href="{{ route('product', $product->slug) }}" aria-label="{{ $product->name }}">
            <img src="{{ asset('storage/' . $product->thumbnail) }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 itemprop="image"
                 class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105"
                 onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22><rect fill=%22%23f3f4f6%22 width=%22200%22 height=%22200%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-size=%2240%22>📦</text></svg>'">
        </a>

        {{-- Discount Badge --}}
        @if($hasDiscount)
        <span class="absolute top-2 right-2 badge-sale">-{{ $discountPercent }}%</span>
        @endif

        {{-- New Badge --}}
        @if(($isNew ?? false) || $product->created_at->diffInDays(now()) <= 14)
        <span class="absolute top-2 {{ $hasDiscount ? 'right-16' : 'right-2' }} bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full">جديد</span>
        @endif

        {{-- Out of Stock Overlay --}}
        @if(!$isInStock && !$product->has_variants)
        <div class="absolute inset-0 bg-white/60 flex items-center justify-center">
            <span class="bg-gray-800 text-white text-xs font-bold px-3 py-1 rounded-full">نفذت الكمية</span>
        </div>
        @endif

        {{-- Wishlist --}}
        <form action="{{ route('wishlist.add') }}" method="POST" class="absolute top-2 left-2">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            @if($primaryWishlistVariant)
            <input type="hidden" name="variant_id" value="{{ $primaryWishlistVariant->id }}">
            @endif
            <button type="submit"
                    class="w-7 h-7 backdrop-blur rounded-full flex items-center justify-center shadow transition-colors {{ $isWishlisted ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-white/90 text-gray-400 hover:text-red-500' }}"
                    aria-label="{{ $isWishlisted ? 'حذف ' . $product->name . ' من المفضلة' : 'إضافة ' . $product->name . ' للمفضلة' }}"
                    title="{{ $isWishlisted ? 'حذف من المفضلة' : 'إضافة للمفضلة' }}">
                <svg class="w-4 h-4" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </form>
    </div>

    <div class="p-3">
        <p class="text-xs text-gray-400 mb-1">{{ $product->category->name ?? '' }}</p>
        <a href="{{ route('product', $product->slug) }}">
            <h3 class="text-sm font-semibold text-gray-800 line-clamp-2 mb-2 hover:text-primary-600 transition-colors" itemprop="name">
                {{ $product->name }}
            </h3>
        </a>

        <div class="flex items-center justify-between" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
            <meta itemprop="priceCurrency" content="MAD">
            <meta itemprop="availability" content="{{ $isInStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}">
            <div>
                <span class="font-bold text-primary-600 text-base" itemprop="price" content="{{ $product->selling_price }}">{{ number_format($product->selling_price) }} درهم</span>
                @if($hasDiscount)
                <span class="text-xs text-gray-400 line-through mr-1">{{ number_format($product->price_before_discount) }} درهم</span>
                @endif
            </div>

            @if($product->has_variants)
            <a href="{{ route('product', $product->slug) }}"
               class="w-8 h-8 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center justify-center transition-colors"
               aria-label="اختيار خيارات {{ $product->name }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </a>
            @else
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit"
                        {{ !$isInStock ? 'disabled' : '' }}
                        class="w-8 h-8 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="أضف {{ $product->name }} للسلة">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </form>
            @endif
        </div>
    </div>
</article>
