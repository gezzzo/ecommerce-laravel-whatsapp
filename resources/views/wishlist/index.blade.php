@extends('layouts.app')

@section('title', 'المفضلة - متجري')
@section('meta_description', 'منتجاتك المفضلة في متجري. احفظ المنتجات التي تعجبك وارجع لها لاحقاً بسهولة.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2" aria-label="مسار التصفح">
        <a href="{{ route('home') }}" class="hover:text-primary-600">الرئيسية</a>
        <span>/</span>
        <span class="text-gray-800">المفضلة</span>
    </nav>

    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">المفضلة</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $wishlistItems->count() }} منتج محفوظ</p>
        </div>
        <a href="{{ route('products') }}" class="hidden sm:inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:border-primary-200 hover:text-primary-600 transition-colors">
            متابعة التسوق
        </a>
    </div>

    @if($wishlistItems->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($wishlistItems as $item)
        <article class="bg-white rounded-2xl border border-gray-100 p-4 animate-fade-up">
            <div class="flex gap-4">
                <a href="{{ route('product', $item->displaySlug) }}" class="shrink-0">
                    <img src="{{ asset('storage/' . ($item->displayImage ?? '')) }}"
                         alt="{{ $item->displayName }}"
                         loading="lazy"
                         class="w-24 h-24 object-cover rounded-xl bg-gray-50"
                         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 96 96%22><rect fill=%22%23f3f4f6%22 width=%2296%22 height=%2296%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-size=%2230%22>📦</text></svg>'">
                </a>

                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-400 mb-1">{{ $item->displayCategory }}</p>
                    <a href="{{ route('product', $item->displaySlug) }}" class="font-semibold text-gray-800 hover:text-primary-600 line-clamp-2 text-sm transition-colors">
                        {{ $item->displayName }}
                    </a>

                    @if($item->variantLabel)
                    <span class="inline-flex mt-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $item->variantLabel }}</span>
                    @endif

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <span class="font-bold text-primary-600">{{ number_format($item->displayPrice ?? 0) }} درهم</span>
                        <span class="text-xs {{ $item->isInStock ? 'text-green-600' : 'text-red-500' }}">
                            {{ $item->isInStock ? 'متوفر' : 'غير متوفر' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $item->productId }}">
                    @if($item->variantId)
                    <input type="hidden" name="variant_id" value="{{ $item->variantId }}">
                    @endif
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit"
                            {{ ! $item->isInStock ? 'disabled' : '' }}
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        إضافة للسلة
                    </button>
                </form>

                <form action="{{ route('wishlist.remove', $item->sku_code) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-11 h-11 border border-gray-200 rounded-xl flex items-center justify-center text-gray-400 hover:text-red-500 hover:border-red-200 transition-colors"
                            aria-label="حذف {{ $item->displayName }} من المفضلة">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </article>
        @endforeach
    </div>
    @else
    <div class="text-center py-20 animate-fade-up">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">المفضلة فارغة!</h2>
        <p class="text-gray-500 mb-6">اضغط على القلب في أي منتج لحفظه هنا.</p>
        <a href="{{ route('products') }}"
           class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-semibold px-8 py-3 rounded-xl transition-colors">
            تصفح المنتجات
        </a>
    </div>
    @endif
</div>
@endsection
