<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - متجري</title>
    <meta name="description" content="سجّل دخولك إلى متجري برقم هاتفك لمتابعة طلباتك والتسوق بسهولة.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { cairo: ['Cairo', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#fef3f2',100:'#fde8e6',500:'#e84340',600:'#d52a27',700:'#b21f1d' }
                    }
                }
            }
        }
    </script>
    <style>* { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-orange-50 flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <a href="{{ route('home') }}" class="flex items-center justify-center gap-3 mb-8">
        <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <span class="text-2xl font-extrabold text-gray-900">متجري</span>
    </a>

    {{-- Card --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-gray-900">مرحباً بعودتك 👋</h1>
            <p class="text-gray-500 text-sm mt-1">سجّل دخولك برقم هاتفك</p>
        </div>

        {{-- Flash --}}
        @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm mb-4">
            ℹ️ {{ session('info') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-4">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                    📱 رقم الهاتف
                </label>
                <div class="relative">
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="{{ old('phone') }}"
                           placeholder="06xxxxxxxx"
                           autocomplete="tel"
                           required
                           class="w-full border {{ $errors->has('phone') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    🔒 كلمة المرور
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       required
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all">
            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="accent-primary-600 w-4 h-4">
                    <span class="text-gray-600">تذكرني</span>
                </label>
            </div>

            {{-- Redirect after login --}}
            @if(request('redirect_to'))
            <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
            @endif

            <button type="submit"
                    id="login-submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all text-base shadow-md shadow-primary-200">
                تسجيل الدخول
            </button>
        </form>

        {{-- Register link — hidden in guest mode --}}
        @if(($mode ?? 'optional') !== 'guest')
        <div class="mt-6 text-center text-sm text-gray-500">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="text-primary-600 font-bold hover:underline">إنشاء حساب جديد</a>
        </div>
        @endif

        <div class="mt-4 text-center">
            <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-gray-600">
                ← العودة للمتجر
            </a>
        </div>
    </div>
</div>

</body>
</html>
