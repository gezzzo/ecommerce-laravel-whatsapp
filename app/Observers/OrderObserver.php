<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('delivery_status')) {
            return;
        }

        if ($order->delivery_status !== 'Livré') {
            return;
        }

        $order->payment_status = 'paid';
        $order->saveQuietly();
    }
}

