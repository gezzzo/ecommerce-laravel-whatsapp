@php
    $latestTracking = $trackingRecords->first();
    $latestStatusColor = $latestTracking?->statut_color ?: '#16a34a';
@endphp

<div dir="rtl" style="font-family: inherit; color: #111827;">
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px;">
        <div style="border: 1px solid #e5e7eb; border-radius: 14px; background: #ffffff; padding: 14px;">
            <div style="font-size: 12px; color: #6b7280; margin-bottom: 6px;">{{ __('Order Number') }}</div>
            <div style="font-size: 15px; font-weight: 700; color: #111827; word-break: break-word;">{{ $order->order_number }}</div>
        </div>

        <div style="border: 1px solid #e5e7eb; border-radius: 14px; background: #ffffff; padding: 14px;">
            <div style="font-size: 12px; color: #6b7280; margin-bottom: 6px;">{{ __('Tracking Number') }}</div>
            <div style="font-size: 15px; font-weight: 700; color: #111827; direction: ltr; text-align: right; word-break: break-word;">
                {{ $order->tracking_number ?? __('order.placeholders.not_specified') }}
            </div>
        </div>

        <div style="border: 1px solid #dcfce7; border-radius: 14px; background: #f0fdf4; padding: 14px;">
            <div style="font-size: 12px; color: #15803d; margin-bottom: 6px;">{{ __('order.tracking.current_status') }}</div>
            <div style="font-size: 15px; font-weight: 800; color: #166534; word-break: break-word;">
                {{ $latestTracking?->statut_name ?? $order->delivery_status ?? __('order.placeholders.not_specified') }}
            </div>
        </div>
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
        <div>
            <div style="font-size: 14px; font-weight: 800; color: #111827;">{{ __('order.tracking.timeline') }}</div>
            <div style="font-size: 12px; color: #6b7280; margin-top: 3px;">
                {{ __('order.tracking.updates_count', ['count' => $trackingRecords->count()]) }}
            </div>
        </div>

        @if($latestTracking?->time)
            <div style="border-radius: 999px; background: #f3f4f6; color: #374151; font-size: 12px; font-weight: 600; padding: 7px 11px;">
                {{ __('order.tracking.latest_update') }}: {{ $latestTracking->time->format('Y-m-d H:i') }}
            </div>
        @endif
    </div>

    <div data-testid="tracking-timeline" style="border: 1px solid #e5e7eb; border-radius: 16px; background: #ffffff; overflow: hidden;">
        @foreach($trackingRecords as $record)
            @php
                $statusColor = $record->statut_color ?: '#16a34a';
                $situationColor = $record->situation_color ?: '#64748b';
            @endphp

            <div style="display: grid; grid-template-columns: 150px 22px 1fr; gap: 12px; padding: 16px; border-bottom: {{ $loop->last ? '0' : '1px solid #f3f4f6' }};">
                <div style="color: #6b7280; font-size: 12px; line-height: 1.6; padding-top: 2px;">
                    <div style="font-weight: 700; color: #374151;">{{ $record->time?->format('H:i') ?? '--:--' }}</div>
                    <div>{{ $record->time?->format('Y-m-d') ?? __('order.placeholders.not_specified') }}</div>
                </div>

                <div style="position: relative; display: flex; justify-content: center;">
                    @if(! $loop->last)
                        <div style="position: absolute; top: 18px; bottom: -18px; width: 2px; background: #e5e7eb;"></div>
                    @endif
                    <div style="position: relative; z-index: 1; width: 14px; height: 14px; border-radius: 999px; background: {{ $statusColor }}; box-shadow: 0 0 0 5px #f3f4f6;"></div>
                </div>

                <div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 8px;">
                        <span style="display: inline-flex; align-items: center; border-radius: 999px; background: {{ $statusColor }}; color: #ffffff; font-size: 12px; font-weight: 800; padding: 6px 10px;">
                            {{ $record->statut_name }}
                        </span>

                        @if($record->situation_name)
                            <span style="display: inline-flex; align-items: center; border-radius: 999px; background: #f8fafc; color: {{ $situationColor }}; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 700; padding: 6px 10px;">
                                {{ $record->situation_name }}
                            </span>
                        @endif
                    </div>

                    @if($record->commentaire)
                        <div style="font-size: 13px; color: #374151; line-height: 1.7;">
                            {{ $record->commentaire }}
                        </div>
                    @endif

                    @if($record->livreur)
                        <div style="font-size: 12px; color: #6b7280; margin-top: 8px;">
                            {{ __('order.tracking.courier') }}: {{ $record->livreur }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
