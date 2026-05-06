<?php

namespace App\Services;

use App\Models\WhatsappInboxMessage;

class WhatsappInboxReplyService
{
    /**
     * Send a direct reply to an inbound WhatsApp message without creating a campaign.
     *
     * @return array{success: bool, data: array<string,mixed>, error: string|null}
     */
    public function reply(WhatsappInboxMessage $inboxMessage, string $message): array
    {
        $session = $inboxMessage->session;

        if (! $session) {
            return [
                'success' => false,
                'data' => [],
                'error' => __('No WhatsApp session is linked to this message.'),
            ];
        }

        if (! $session->isConnected()) {
            return [
                'success' => false,
                'data' => [],
                'error' => __('Please connect this message session before replying.'),
            ];
        }

        $message = trim($message);

        if ($message === '') {
            return [
                'success' => false,
                'data' => [],
                'error' => __('Reply message cannot be empty.'),
            ];
        }

        $result = (new WhatsappService($session))->sendText(
            phone: $inboxMessage->from,
            message: $message,
        );

        if ($result['success']) {
            $inboxMessage->markAsRead();
        }

        return $result;
    }
}
