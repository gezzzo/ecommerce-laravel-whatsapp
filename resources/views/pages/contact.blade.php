@extends('layouts.app')

@section('title', 'تواصل معنا - متجري')
@section('meta_description', 'تواصل مع فريق خدمة عملاء متجري. نحن هنا لمساعدتك على مدار الساعة.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6">تواصل معنا</h1>
    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">معلومات التواصل</h2>
            <ul class="space-y-3 text-sm text-gray-600">
                <li class="flex items-center gap-2">📞 <span>01000000000</span></li>
                <li class="flex items-center gap-2">📧 <span>info@mystore.com</span></li>
                <li class="flex items-center gap-2">⏰ <span>يومياً من 9 صباحاً حتى 10 مساءً</span></li>
            </ul>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-4">أرسل لنا رسالة</h2>
            <form class="space-y-3">
                <input type="text" placeholder="الاسم" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                <input type="email" placeholder="البريد الإلكتروني" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                <textarea placeholder="رسالتك" rows="4" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"></textarea>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">إرسال</button>
            </form>
        </div>
    </div>
</div>
@endsection
