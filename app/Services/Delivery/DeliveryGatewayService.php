<?php

namespace App\Services\Delivery;

use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Services\Delivery\Handlers\DeliveryHandlerInterface;
use App\Services\Delivery\Handlers\OzoneHandler;

class DeliveryGatewayService
{
    /**
     * Resolve the appropriate handler for a delivery company.
     */
    public function getHandler(DeliveryCompany $company): DeliveryHandlerInterface
    {
        $slug = strtolower($company->provider->slug);

        return match ($slug) {
            'ozone' => new OzoneHandler(),
            default => throw new \RuntimeException("Unsupported delivery provider: {$slug}"),
        };
    }

    /**
     * Send an order to the delivery company.
     *
     * @return array<string, mixed>
     */
    public function send(Order $order, DeliveryCompany $company): array
    {
        return $this->getHandler($company)->send($order, $company);
    }

    /**
     * Track an order with the delivery company.
     */
    public function track(Order $order, DeliveryCompany $company): string
    {
        return $this->getHandler($company)->track($order, $company);
    }
}
