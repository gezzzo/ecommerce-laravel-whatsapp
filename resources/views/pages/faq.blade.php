@extends('layouts.app')

@section('title', 'الأسئلة الشائعة - متجري')
@section('meta_description', 'إجابات على الأسئلة الشائعة حول التسوق والشحن والدفع والإرجاع في متجري.')

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        {
            "@@type": "Question",
            "name": "كم تستغرق عملية الشحن؟",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "يتم الشحن خلال 2-5 أيام عمل لجميع المحافظات."
            }
        },
        {
            "@@type": "Question",
            "name": "هل يمكنني إرجاع المنتج؟",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "نعم، يمكنك إرجاع المنتج خلال 30 يوم من الاستلام بشرط أن يكون في حالته الأصلية."
            }
        },
        {
            "@@type": "Question",
            "name": "ما هي طرق الدفع المتاحة؟",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "نقبل الدفع عند الاستلام، البطاقات البنكية، والمحافظ الإلكترونية."
            }
        }
    ]
}
</script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6">الأسئلة الشائعة</h1>
    <div class="space-y-4">
        @foreach([
            ['q' => 'كم تستغرق عملية الشحن؟', 'a' => 'يتم الشحن خلال 2-5 أيام عمل لجميع المحافظات.'],
            ['q' => 'هل يمكنني إرجاع المنتج؟', 'a' => 'نعم، يمكنك إرجاع المنتج خلال 30 يوم من الاستلام بشرط أن يكون في حالته الأصلية.'],
            ['q' => 'ما هي طرق الدفع المتاحة؟', 'a' => 'نقبل الدفع عند الاستلام، البطاقات البنكية، والمحافظ الإلكترونية.'],
            ['q' => 'هل الشحن مجاني؟', 'a' => 'نعم، الشحن مجاني على الطلبات فوق 200 درهم.'],
            ['q' => 'كيف أتتبع طلبي؟', 'a' => 'يمكنك تتبع طلبك من خلال صفحة "حسابي" بعد تسجيل الدخول.'],
        ] as $faq)
        <details class="bg-white rounded-2xl border border-gray-100 p-4 group">
            <summary class="font-semibold text-gray-800 cursor-pointer flex items-center justify-between">
                {{ $faq['q'] }}
                <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <p class="text-sm text-gray-600 mt-3 leading-relaxed">{{ $faq['a'] }}</p>
        </details>
        @endforeach
    </div>
</div>
@endsection
