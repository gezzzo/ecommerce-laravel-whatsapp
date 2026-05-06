@php
    $siteName = $storeName ?? \App\Models\StoreSetting::storeName();
    $siteContactInfo = $contactInfo ?? \App\Models\StoreSetting::contactInfo();
    $siteMetaPixelId = $metaPixelId ?? \App\Models\StoreSetting::metaPixelId();
    $sitePhone = $siteContactInfo['phone'] ?? '';
    $siteEmail = $siteContactInfo['email'] ?? '';
    $siteWorkingHours = $siteContactInfo['working_hours'] ?? '';
    $sitePhoneHref = filled($sitePhone) ? 'tel:' . preg_replace('/[^\d+]/', '', $sitePhone) : null;
    $siteEmailHref = filled($siteEmail) ? 'mailto:' . $siteEmail : null;
    $siteSocialLinks = [
        ['label' => 'f', 'name' => 'Facebook', 'url' => $siteContactInfo['facebook_url'] ?? ''],
        ['label' => 'in', 'name' => 'Instagram', 'url' => $siteContactInfo['instagram_url'] ?? ''],
        ['label' => 'tw', 'name' => 'Twitter', 'url' => $siteContactInfo['twitter_url'] ?? ''],
    ];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <title>@yield('title', $siteName . ' - ملابس نسائية مغربية راقية')</title>
    <meta name="description" content="@yield('meta_description', $siteName . ' - متجرك الأول في المغرب لأرقى الملابس النسائية والمحجبات، جلابة مغربية، فساتين وعبايات. جودة عالية بأسعار مناسبة مع شحن سريع لجميع مدن المغرب.')">
    <meta name="keywords" content="@yield('meta_keywords', 'ملابس نسائية, أزياء مغربية, جلابة مغربية, ملابس محجبات, فساتين, تسوق ملابس, المغرب, قفطان, عبايات, Mohtachima')">
    <meta name="robots" content="index, follow">
    <meta name="author" content="{{ $siteName }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', $siteName . ' - ملابس نسائية مغربية')">
    <meta property="og:description" content="@yield('meta_description', $siteName . ' - متجرك الأول في المغرب لأرقى الملابس النسائية والمحجبات. شحن سريع لجميع المدن.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og_image.png'))">
    <meta property="og:locale" content="ar_MA">
    <meta property="og:site_name" content="{{ $siteName }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', $siteName . ' - ملابس نسائية مغربية')">
    <meta name="twitter:description" content="@yield('meta_description', $siteName . ' - متجرك الأول في المغرب لأرقى الملابس النسائية والمحجبات. شحن سريع لجميع المدن.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og_image.png'))">

    @if(filled($siteMetaPixelId))
        {{-- Meta Pixel --}}
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($siteMetaPixelId));
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ urlencode($siteMetaPixelId) }}&ev=PageView&noscript=1"
                 alt="">
        </noscript>
    @endif

    {{-- Structured Data - Organization --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": @json($siteName),
        "url": @json(url('/')),
        "logo": @json(asset('images/logo.png')),
        "email": @json($siteEmail),
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": @json($sitePhone),
            "contactType": "customer service",
            "availableLanguage": "Arabic"
        }
    }
    </script>

    {{-- Structured Data - WebSite (for Sitelinks Search) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "url": @json(url('/')),
        "name": @json($siteName),
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ route('search') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    @stack('structured_data')

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CDN (للتطوير - استبدل بـ vite في الإنتاج) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50:  '#fef3f2',
                            100: '#fde8e6',
                            200: '#fbd0cc',
                            300: '#f8a9a3',
                            400: '#f2716a',
                            500: '#e84340',
                            600: '#d52a27',
                            700: '#b21f1d',
                            800: '#941f1d',
                            900: '#7b1f1e',
                        },
                        accent: '#ff6b35',
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Cairo', sans-serif; }
        .gradient-hero { background: linear-gradient(135deg, #fff5f4 0%, #ffeae8 50%, #fff0ec 100%); }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.1); }
        .badge-sale { background: #e84340; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 20px; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        @keyframes fadeInDown { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
        .animate-fade { animation: fadeInDown .3s ease; }
        .animate-fade-up { animation: fadeInUp .4s ease; }
        /* Color swatch active state */
        .color-swatch { transition: all .2s; cursor: pointer; }
        .color-swatch.active, .color-swatch:hover { transform: scale(1.15); box-shadow: 0 0 0 3px rgba(213,42,39,.4); }
        .size-btn { transition: all .2s; }
        .size-btn.active { background: #d52a27; color: #fff; border-color: #d52a27; }
        /* Image gallery */
        .gallery-thumb { transition: border-color .2s; }
        .gallery-thumb.active { border-color: #d52a27 !important; }
        /* scrollbar */
        ::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-thumb { background: #e84340; border-radius: 3px; }
        /* Skeleton loading */
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800">

    {{-- Top bar --}}
    @if(filled($announcementBarText ?? null))
    <div class="bg-primary-600 text-white text-center text-sm py-2 px-4">
        <span>{{ $announcementBarText }}</span>
    </div>
    @endif

    {{-- Navbar --}}
    <header class="bg-white shadow-sm sticky top-0 z-50" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 gap-4">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0" aria-label="الصفحة الرئيسية">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ $siteName }}" class="h-12 w-auto">
                </a>

                {{-- Search --}}
                <form action="{{ route('search') }}" method="GET" class="flex-1 max-w-xl hidden md:flex" role="search">
                    <div class="relative w-full">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="ابحث عن منتج..."
                               aria-label="البحث عن منتج"
                               class="w-full border border-gray-200 rounded-xl pr-4 pl-12 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-gray-50">
                        <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-600" aria-label="بحث">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                        </button>
                    </div>
                </form>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    @php $checkoutMode = \App\Models\StoreSetting::checkoutMode(); @endphp

                    @auth
                    <div class="hidden md:flex items-center gap-3">
                        <a href="{{ route('account') }}" class="flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ Str::limit(auth()->user()->name, 12) }}</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-400 hover:text-red-500 transition-colors" aria-label="تسجيل الخروج">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                    @elseif($checkoutMode !== 'guest')
                    <a href="{{ route('login') }}" class="hidden md:block text-sm text-gray-600 hover:text-primary-600">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="hidden md:block text-sm bg-primary-600 hover:bg-primary-700 text-white px-4 py-1.5 rounded-lg transition-colors">حساب جديد</a>
                    @endauth

                    <a href="{{ route('wishlist') }}" class="relative p-2 text-gray-600 hover:text-primary-600" aria-label="المفضلة">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @if(($wishlistCount ?? session('wishlist_count', 0)) > 0)
                        <span class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $wishlistCount ?? session('wishlist_count') }}</span>
                        @endif
                    </a>

                    <a href="{{ route('cart') }}" class="relative p-2 text-gray-600 hover:text-primary-600" aria-label="سلة التسوق">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @if(session('cart_count', 0) > 0)
                        <span class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ session('cart_count') }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- Categories Nav --}}
        <nav class="border-t border-gray-100 bg-white" aria-label="تصفح الفئات">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 flex gap-6 overflow-x-auto py-2 text-sm font-medium scrollbar-hide">
                <a href="{{ route('home') }}" class="whitespace-nowrap {{ request()->routeIs('home') ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-600 hover:text-primary-600' }} pb-1">الرئيسية</a>
                @foreach($navCategories ?? [] as $cat)
                <a href="{{ route('category', $cat->slug) }}" class="whitespace-nowrap text-gray-600 hover:text-primary-600 pb-1">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('offers') }}" class="whitespace-nowrap text-red-500 font-bold pb-1">🔥 العروض</a>
            </div>
        </nav>
    </header>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 text-sm animate-fade max-w-7xl mx-auto mt-4 rounded-lg" role="alert">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 text-sm animate-fade max-w-7xl mx-auto mt-4 rounded-lg" role="alert">
        ❌ {{ session('error') }}
    </div>
    @endif

    {{-- Main Content --}}
    <main role="main">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300 mt-16" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ $siteName }}" class="h-8 w-auto">
                    <span class="text-white font-bold text-lg">{{ $siteName }}</span>
                </div>
                <p class="text-sm leading-relaxed">متجرك الإلكتروني الأول لأفضل المنتجات بأسعار منافسة وشحن سريع.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">روابط سريعة</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white">الرئيسية</a></li>
                    <li><a href="{{ route('products') }}" class="hover:text-white">جميع المنتجات</a></li>
                    <li><a href="{{ route('offers') }}" class="hover:text-white">العروض</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">من نحن</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">خدمة العملاء</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('contact') }}" class="hover:text-white">تواصل معنا</a></li>
                    <li><a href="{{ route('orders') }}" class="hover:text-white">تتبع طلبك</a></li>
                    <li><a href="{{ route('returns') }}" class="hover:text-white">سياسة الإرجاع</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white">الأسئلة الشائعة</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">تواصل معنا</h4>
                <ul class="space-y-2 text-sm">
                    @if(filled($sitePhone))
                    <li>📞 <a href="{{ $sitePhoneHref }}" class="hover:text-white">{{ $sitePhone }}</a></li>
                    @endif
                    @if(filled($siteEmail))
                    <li>📧 <a href="{{ $siteEmailHref }}" class="hover:text-white">{{ $siteEmail }}</a></li>
                    @endif
                    @if(filled($siteWorkingHours))
                    <li>⏰ {{ $siteWorkingHours }}</li>
                    @endif
                </ul>
                <div class="flex gap-3 mt-4">
                    @foreach($siteSocialLinks as $socialLink)
                        @if(filled($socialLink['url']))
                        <a href="{{ $socialLink['url'] }}" class="w-8 h-8 bg-gray-700 rounded-lg flex items-center justify-center hover:bg-primary-600 transition-colors text-xs" aria-label="{{ $socialLink['name'] }}" target="_blank" rel="noopener">{{ $socialLink['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 text-center py-4 text-xs text-gray-500">
            © {{ date('Y') }} {{ $siteName }}. جميع الحقوق محفوظة.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
