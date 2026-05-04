<?php

namespace App\Services\Delivery\Handlers;

use App\Models\DeliveryCompany;
use App\Models\Order;

interface DeliveryHandlerInterface
{
    /**
     * Send an order to the delivery company.
     *
     * @return array<string, mixed>
     */
    public function send(Order $order, DeliveryCompany $company): array;

    /**
     * Track an order with the delivery company.
     */
    public function track(Order $order, DeliveryCompany $company): string;
}
