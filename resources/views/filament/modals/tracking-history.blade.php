<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="grid gap-3 text-sm md:grid-cols-3">
            <div>
                <div class="text-xs text-gray-500">{{ __('Order Number') }}</div>
                <div class="font-semibold text-gray-900">{{ $order->order_number }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Tracking Number') }}</div>
                <div class="font-semibold text-gray-900">{{ $order->tracking_number ?? __('order.placeholders.not_specified') }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">{{ __('Delivery Status') }}</div>
                <div class="font-semibold text-gray-900">{{ $order->delivery_status ?? __('order.placeholders.not_specified') }}</div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="divide-y divide-gray-100">
            @foreach($trackingRecords as $record)
                <div class="grid gap-3 p-4 text-sm md:grid-cols-[10rem_1fr]">
                    <div class="text-gray-500">
                        {{ $record->time?->format('Y-m-d H:i') ?? __('order.placeholders.not_specified') }}
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                  style="background-color: {{ $record->statut_color ?: '#f3f4f6' }}22; color: {{ $record->statut_color ?: '#374151' }}">
                                {{ $record->statut_name }}
                            </span>

                            @if($record->situation_name)
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                      style="background-color: {{ $record->situation_color ?: '#f3f4f6' }}22; color: {{ $record->situation_color ?: '#374151' }}">
                                    {{ $record->situation_name }}
                                </span>
                            @endif
                        </div>

                        @if($record->livreur)
                            <div class="text-gray-600">{{ __('order.tracking.courier') }}: {{ $record->livreur }}</div>
                        @endif

                        @if($record->commentaire)
                            <div class="text-gray-600">{{ $record->commentaire }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
