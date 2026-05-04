@extends('layouts.app')

@section('title', 'تأكيد الطلب عبر واتساب - متجري')
@section('meta_description', 'أكد طلبك عبر واتساب حتى نبدأ تجهيز الشحن.')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 bg-green-50 text-green-700 rounded-full px-3 py-1 text-sm font-semibold mb-4">
                        ✅ تم إنشاء الطلب
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                        أكد طلبك من واتساب
                    </h1>
                    <p class="text-gray-500 leading-7">
                        اضغط على زر واتساب وسيتم فتح رسالة جاهزة لتأكيد الطلب. بعد إرسالها سنحفظ رقم واتساب على الطلب ونرسل لك تفاصيل الطلب في المحادثة، ويمكنك إرسال كود الطلب لاحقاً لتتبع الأوردر.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4 min-w-64">
                    <div class="text-sm text-gray-500 mb-1">رقم الطلب</div>
                    <div class="font-bold text-xl text-gray-900">{{ $order->order_number }}</div>
                    <div class="mt-3 text-sm text-gray-500">الإجمالي</div>
                    <div class="font-bold text-primary-600 text-xl">{{ number_format((float) $order->total, 2) }} درهم</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-[1fr_320px] gap-0">
            <div class="p-6 sm:p-8">
                <div class="bg-primary-50 border border-primary-100 rounded-2xl p-5 mb-6">
                    <div class="text-sm font-semibold text-primary-700 mb-2">رسالة التأكيد الجاهزة</div>
                    <div class="bg-white rounded-xl border border-primary-100 p-4 text-gray-900 font-semibold">
                        {{ $confirmationMessage }}
                    </div>

                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}"
                           target="_blank"
                           rel="noopener"
                           class="mt-4 inline-flex w-full sm:w-auto items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-xl transition-colors">
                            فتح واتساب وتأكيد الطلب
                        </a>
                    @else
                        <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 text-sm">
                            واتساب التأكيد غير متاح حالياً. سنراجع الطلب ونتواصل معك قريباً.
                        </div>
                    @endif
                </div>

                <h2 class="font-bold text-gray-900 mb-4">بيانات الشحن</h2>
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="text-gray-400 mb-1">الاسم</div>
                        <div class="font-semibold text-gray-900">{{ $order->name }}</div>
                    </div>
                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="text-gray-400 mb-1">رقم الهاتف</div>
                        <div class="font-semibold text-gray-900">{{ $order->phone }}</div>
                    </div>
                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="text-gray-400 mb-1">المدينة</div>
                        <div class="font-semibold text-gray-900">{{ $order->city }}</div>
                    </div>
                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="text-gray-400 mb-1">طريقة الدفع</div>
                        <div class="font-semibold text-gray-900">الدفع عند الاستلام</div>
                    </div>
                    <div class="sm:col-span-2 border border-gray-100 rounded-xl p-4">
                        <div class="text-gray-400 mb-1">العنوان</div>
                        <div class="font-semibold text-gray-900">{{ $order->address }}</div>
                    </div>
                </div>
            </div>

            <aside class="bg-gray-50 p-6 sm:p-8 border-t lg:border-t-0 lg:border-r border-gray-100">
                <h2 class="font-bold text-gray-900 mb-4">ملخص الطلب</h2>
                <div class="space-y-4 mb-6">
                    @foreach($order->items as $item)
                        @php
                            $skuable = $item->skuCode?->skuable;
                            $itemName = match (true) {
                                $skuable instanceof \App\Models\Variant => $skuable->product?->name ?? 'منتج',
                                $skuable instanceof \App\Models\Product => $skuable->name,
                                default => 'منتج',
                            };
                        @endphp
                        <div class="flex justify-between gap-3 text-sm">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $itemName }}</div>
                                <div class="text-gray-400">{{ $item->quantity }} × {{ number_format((float) $item->price, 2) }} درهم</div>
                            </div>
                            <div class="font-bold text-gray-900">{{ number_format((float) $item->subtotal, 2) }} درهم</div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">المجموع الفرعي</span>
                        <span>{{ number_format((float) $order->subtotal, 2) }} درهم</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">الشحن</span>
                        <span>{{ number_format((float) $order->shipping, 2) }} درهم</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-2">
                        <span>الإجمالي</span>
                        <span class="text-primary-600">{{ number_format((float) $order->total, 2) }} درهم</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
