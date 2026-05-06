@extends('layouts.app')

@php
    $pageStoreName = $storeName ?? \App\Models\StoreSetting::storeName();
    $pageContactInfo = $contactInfo ?? \App\Models\StoreSetting::contactInfo();
    $phone = $pageContactInfo['phone'] ?? '';
    $email = $pageContactInfo['email'] ?? '';
    $workingHours = $pageContactInfo['working_hours'] ?? '';
    $phoneHref = filled($phone) ? 'tel:' . preg_replace('/[^\d+]/', '', $phone) : null;
    $emailHref = filled($email) ? 'mailto:' . $email : null;
    $socialLinks = [
        ['label' => 'f', 'name' => 'Facebook', 'url' => $pageContactInfo['facebook_url'] ?? ''],
        ['label' => 'in', 'name' => 'Instagram', 'url' => $pageContactInfo['instagram_url'] ?? ''],
        ['label' => 'tw', 'name' => 'Twitter', 'url' => $pageContactInfo['twitter_url'] ?? ''],
    ];
@endphp

@section('title', 'تواصل معنا - ' . $pageStoreName)
@section('meta_description', 'تواصل مع فريق خدمة عملاء ' . $pageStoreName . '. نحن هنا لمساعدتك.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2" aria-label="مسار التصفح">
        <a href="{{ route('home') }}" class="hover:text-primary-600">الرئيسية</a>
        <span>/</span>
        <span class="text-gray-800">تواصل معنا</span>
    </nav>

    <div class="mb-10">
        <p class="text-primary-600 font-semibold mb-2">خدمة العملاء</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">تواصل معنا</h1>
        <p class="mt-3 text-gray-600 max-w-2xl leading-relaxed">
            فريق {{ $pageStoreName }} جاهز لمساعدتك في الطلبات، الشحن، الاستبدال، وأي استفسار قبل الشراء.
        </p>
    </div>

    <div class="grid lg:grid-cols-3 gap-5 mb-8">
        @if(filled($phone))
        <a href="{{ $phoneHref }}" class="bg-white rounded-2xl border border-gray-100 p-6 hover:border-primary-200 hover:shadow-lg transition-all">
            <span class="w-12 h-12 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center text-2xl mb-4">📞</span>
            <h2 class="font-bold text-gray-900 mb-1">رقم الهاتف</h2>
            <p class="text-gray-600 text-left" dir="ltr">{{ $phone }}</p>
        </a>
        @endif

        @if(filled($email))
        <a href="{{ $emailHref }}" class="bg-white rounded-2xl border border-gray-100 p-6 hover:border-primary-200 hover:shadow-lg transition-all">
            <span class="w-12 h-12 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center text-2xl mb-4">📧</span>
            <h2 class="font-bold text-gray-900 mb-1">البريد الإلكتروني</h2>
            <p class="text-gray-600 break-all">{{ $email }}</p>
        </a>
        @endif

        @if(filled($workingHours))
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <span class="w-12 h-12 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center text-2xl mb-4">⏰</span>
            <h2 class="font-bold text-gray-900 mb-1">مواعيد العمل</h2>
            <p class="text-gray-600">{{ $workingHours }}</p>
        </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">راسلنا مباشرة</h2>
            <p class="text-gray-600 leading-relaxed mb-6">
                أسرع طريقة للتواصل هي الاتصال أو إرسال رسالة على البريد الإلكتروني المسجل في إعدادات المتجر.
            </p>

            <div class="flex flex-col sm:flex-row gap-3">
                @if(filled($phone))
                <a href="{{ $phoneHref }}" class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                    📞 اتصال الآن
                </a>
                @endif

                @if(filled($email))
                <a href="{{ $emailHref }}" class="inline-flex items-center justify-center gap-2 border border-gray-200 hover:border-primary-200 text-gray-700 hover:text-primary-600 font-semibold px-6 py-3 rounded-xl transition-colors">
                    📧 إرسال بريد
                </a>
                @endif
            </div>
        </div>

        <div class="bg-gray-900 text-white rounded-2xl p-6 sm:p-8">
            <h2 class="text-xl font-bold mb-4">تابعنا</h2>
            <p class="text-gray-300 text-sm leading-relaxed mb-6">
                روابط التواصل الاجتماعي يتم تعديلها من لوحة التحكم ضمن إعدادات المتجر.
            </p>
            <div class="flex gap-3">
                @foreach($socialLinks as $socialLink)
                    @if(filled($socialLink['url']))
                    <a href="{{ $socialLink['url'] }}" class="w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary-600 transition-colors text-sm font-bold" aria-label="{{ $socialLink['name'] }}" target="_blank" rel="noopener">
                        {{ $socialLink['label'] }}
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
