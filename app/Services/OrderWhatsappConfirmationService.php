<?php

namespace App\Services;

use App\Enums\WhatsappSessionStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use App\Models\WhatsappSession;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class OrderWhatsappConfirmationService
{
    public function confirmationMessage(Order $order): string
    {
        return implode("\n", [
            "لتأكيد الاوردر رقم: {$order->order_number}",
            'لتتبع الاوردر الخاص بك ارسل لنا هذا الكود فقط:',
            $order->order_number,
        ]);
    }

    public function confirmationUrl(Order $order): ?string
    {
        $session = $this->confirmationSession();

        if (! $session?->phone_number) {
            return null;
        }

        $phone = $this->formatPhoneForWhatsappLink($session->phone_number);

        if (blank($phone)) {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->confirmationMessage($order));
    }

    public function confirmationSession(): ?WhatsappSession
    {
        return WhatsappSession::query()
            ->where('status', WhatsappSessionStatus::Connected->value)
            ->whereNotNull('phone_number')
            ->latest('connected_at')
            ->latest('id')
            ->first();
    }

    public function confirmFromIncomingMessage(
        string $from,
        ?string $text,
        ?string $messageId = null
    ): ?Order {
        if (! $this->hasConfirmationIntent($text ?? '')) {
            return null;
        }

        $orderNumber = $this->extractOrderNumber($text ?? '');

        if (! $orderNumber) {
            return null;
        }

        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return null;
        }

        $order->update([
            'status' => 'confirmed',
            'whatsapp_phone' => $this->normalizeIncomingPhone($from),
            'whatsapp_confirmed_at' => $order->whatsapp_confirmed_at ?? now(),
            'whatsapp_confirmation_message_id' => $messageId,
        ]);

        return $order->refresh();
    }

    public function orderFromIncomingMessage(?string $text): ?Order
    {
        $orderNumber = $this->extractOrderNumber($text ?? '');

        if (! $orderNumber) {
            return null;
        }

        return Order::query()
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function confirmationReply(Order $order): string
    {
        $order->loadMissing([
            'items.skuCode.skuable' => function ($morphTo): void {
                $morphTo->morphWith([
                    Product::class => [],
                    Variant::class => ['product', 'size', 'color'],
                ]);
            },
        ]);

        $items = $order->items
            ->map(function ($item): string {
                $skuable = $item->skuCode?->skuable;
                $name = match (true) {
                    $skuable instanceof Variant => $skuable->product?->name ?? 'منتج',
                    $skuable instanceof Product => $skuable->name,
                    default => 'منتج',
                };

                return "- {$name} × {$item->quantity}";
            })
            ->implode("\n");

        return implode("\n", array_filter([
            'تم تأكيد طلبك بنجاح ✅',
            "رقم الطلب: {$order->order_number}",
            "الاسم: {$order->name}",
            "المدينة: {$order->city}",
            "العنوان: {$order->address}",
            filled($items) ? "المنتجات:\n{$items}" : null,
            'الإجمالي: '.Number::format((float) $order->total, 2).' درهم',
            'الدفع: '.$this->paymentMethodLabel($order->payment_method),
            "لتتبع الاوردر الخاص بك ارسل لنا هذا الكود فقط: {$order->order_number}",
            'سنتواصل معك قريباً لتجهيز الطلب.',
        ]));
    }

    public function trackingReply(Order $order): string
    {
        $order->loadMissing([
            'trackingParcels' => fn ($query) => $query->latest('time')->latest('id'),
        ]);

        $latestTracking = $order->trackingParcels->first();

        return implode("\n", array_filter([
            'تتبع طلبك 🔎',
            "رقم الطلب: {$order->order_number}",
            'حالة الطلب: '.($order->status_label ?: 'قيد المراجعة'),
            $order->tracking_number ? "رقم الشحنة: {$order->tracking_number}" : 'رقم الشحنة: لم يصدر بعد',
            $latestTracking?->statut_name ? "آخر تحديث: {$latestTracking->statut_name}" : null,
            $latestTracking?->situation_name ? "الوضع الحالي: {$latestTracking->situation_name}" : null,
            $latestTracking?->time ? 'وقت التحديث: '.$latestTracking->time->format('Y-m-d H:i') : null,
            'الإجمالي: '.Number::format((float) $order->total, 2).' درهم',
        ]));
    }

    private function hasConfirmationIntent(string $text): bool
    {
        return Str::contains(Str::lower($text), ['تأكيد', 'تاكيد', 'confirm']);
    }

    private function extractOrderNumber(string $text): ?string
    {
        if (preg_match('/\b(ORD-[A-Z0-9]+)\b/iu', $text, $matches)) {
            return Str::upper($matches[1]);
        }

        return null;
    }

    private function normalizeIncomingPhone(string $from): string
    {
        return User::normalizePhone(Str::before($from, '@'));
    }

    private function formatPhoneForWhatsappLink(string $phone): string
    {
        $digits = User::normalizePhone($phone);

        if (str_starts_with($digits, '0')) {
            return '2'.$digits;
        }

        return $digits;
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cod' => 'الدفع عند الاستلام',
            'card' => 'بطاقة',
            'wallet' => 'محفظة',
            default => $paymentMethod ?? '',
        };
    }
}
