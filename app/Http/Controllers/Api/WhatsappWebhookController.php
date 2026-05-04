<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappInboxMessage;
use App\Models\WhatsappSession;
use App\Services\OrderWhatsappConfirmationService;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    /**
     * Handle incoming MegaMsg webhook POST requests.
     *
     * Configure this URL in your MegaMsg instance settings:
     *   POST https://your-app.com/api/whatsapp/webhook/{instance_id}
     *
     * The {instance_id} segment is used to identify which session
     * the message belongs to, so configure one webhook URL per instance.
     */
    public function receive(
        Request $request,
        string $instanceId,
        OrderWhatsappConfirmationService $confirmationService
    ): JsonResponse {
        $payload = $request->all();

        Log::info('WhatsApp webhook received', [
            'instance_id' => $instanceId,
            'payload' => $payload,
        ]);

        // Only handle "message" type events
        if (($payload['type'] ?? '') !== 'message') {
            return response()->json(['ok' => true, 'message' => 'Event ignored.']);
        }

        $message = $payload['message'] ?? [];

        // Skip group messages (optional — remove check to store group messages too)
        if ($message['is_group'] ?? false) {
            return response()->json(['ok' => true, 'message' => 'Group message ignored.']);
        }

        // Resolve the session by instance_id
        $session = WhatsappSession::where('instance_id', $instanceId)->first();

        // Prevent duplicate processing (MegaMsg retries 3 times)
        $messageId = $message['id'] ?? null;
        if (! $messageId || WhatsappInboxMessage::where('message_id', $messageId)->exists()) {
            return response()->json(['ok' => true, 'message' => 'Duplicate skipped.']);
        }

        $messageType = $this->resolveMessageType($message['type'] ?? 'text');

        $inboxMessage = WhatsappInboxMessage::create([
            'whatsapp_session_id' => $session?->id,
            'message_id' => $messageId,
            'from' => $message['from'] ?? 'unknown',
            'push_name' => $message['push_name'] ?? null,
            'text' => $message['text'] ?? null,
            'message_type' => $messageType,
            'is_group' => $message['is_group'] ?? false,
            'is_read' => false,
            'raw_payload' => $payload,
            'received_at' => now(),
        ]);

        if ($session && $messageType === 'text') {
            $order = $confirmationService->confirmFromIncomingMessage(
                from: $message['from'] ?? '',
                text: $message['text'] ?? '',
                messageId: $messageId,
            );

            if ($order) {
                $inboxMessage->markAsRead();

                $result = (new WhatsappService($session))->sendText(
                    $order->whatsapp_phone ?: ($message['from'] ?? ''),
                    $confirmationService->confirmationReply($order),
                );

                if (! $result['success']) {
                    Log::warning('WhatsApp order confirmation reply failed', [
                        'order_number' => $order->order_number,
                        'whatsapp_phone' => $order->whatsapp_phone,
                        'error' => $result['error'],
                    ]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Normalise MegaMsg message type to our enum values.
     */
    private function resolveMessageType(string $type): string
    {
        return in_array($type, ['text', 'image', 'audio', 'video', 'document', 'sticker', 'location', 'contact'])
            ? $type
            : 'unknown';
    }
}
