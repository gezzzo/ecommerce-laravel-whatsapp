<?php

namespace App\Services\Delivery;

use App\Models\DeliveryCompany;
use App\Models\Order;

class OrderDeliveryService
{
    /**
     * Send the order to the currently active delivery company.
     *
     * @return array{company: DeliveryCompany, order: Order, result: array<string, mixed>}
     */
    public function __construct(
        private readonly DeliveryGatewayService $gateway,
    ) {}

    public function sendToActiveCompany(Order $order): array
    {
        $order->loadMissing(['deliveryZone', 'deliveryCompany']);

        $company = DeliveryCompany::query()
            ->with('provider')
            ->where('is_active', true)
            ->oldest('id')
            ->first();

        if (! $company) {
            throw new \RuntimeException(__('No active delivery company found.'));
        }

        if (! $order->deliveryZone) {
            throw new \RuntimeException(__('Please choose a delivery zone before sending this order.'));
        }

        if ((int) $order->deliveryZone->delivery_company_id !== (int) $company->id) {
            throw new \RuntimeException(__('city belongs to another company please change it.'));
        }

        if ($order->delivery_company_id && (int) $order->delivery_company_id !== (int) $company->id) {
            throw new \RuntimeException(__('city belongs to another company please change it.'));
        }

        if (! $order->delivery_company_id) {
            $order->forceFill([
                'delivery_company_id' => $company->id,
            ])->save();
        }

        $result = $this->gateway->send($order->fresh(['deliveryZone', 'deliveryCompany']), $company);
        $freshOrder = $order->fresh(['deliveryZone', 'deliveryCompany']);

        if (! $freshOrder->delivery_status) {
            $freshOrder->forceFill(['delivery_status' => 'Sent'])->save();
            $freshOrder->refresh();
        }

        return [
            'company' => $company,
            'order' => $freshOrder,
            'result' => $result,
        ];
    }
}
