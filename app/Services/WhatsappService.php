<?php

namespace App\Services;

use App\Enums\WhatsappMediaType;
use App\Models\WhatsappSession;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    private const BASE_URL = 'https://megamsg.app/api';

    public function __construct(public readonly WhatsappSession $session) {}

    /**
     * Send a plain text message.
     *
     * @return array{success: bool, data: array<string,mixed>, error: string|null}
     */
    public function sendText(string $phone, string $message): array
    {
        return $this->request([
            'phone_number' => $this->formatPhone($phone),
            'type' => 'text',
            'text' => $message,
        ]);
    }

    /**
     * Send a media message (image, video, document, audio).
     *
     * @return array{success: bool, data: array<string,mixed>, error: string|null}
     */
    public function sendMedia(
        string $phone,
        WhatsappMediaType $type,
        string $mediaUrl,
        string $message,
        string $caption = ''
    ): array {
        if ($type === WhatsappMediaType::None) {
            throw new \InvalidArgumentException('Cannot send media of type "none".');
        }

        $payload = [
            'phone_number' => $this->formatPhone($phone),
            'type' => $type->value,
            'url' => $mediaUrl,
        ];

        // For image/video/document a caption is sent alongside
        if (in_array($type, [WhatsappMediaType::Image, WhatsappMediaType::Video, WhatsappMediaType::Document])) {
            $payload['caption'] = filled($caption) ? $caption : $message;
        }

        // For audio/document the filename is useful
        if ($type === WhatsappMediaType::Document) {
            $payload['filename'] = basename(parse_url($mediaUrl, PHP_URL_PATH));
        }

        return $this->request($payload);
    }

    /**
     * Send a campaign message (auto-selects text or media).
     *
     * @return array{success: bool, data: array<string,mixed>, error: string|null}
     */
    public function sendCampaignMessage(
        string $phone,
        string $message,
        WhatsappMediaType $mediaType,
        ?string $mediaUrl,
        string $caption = ''
    ): array {
        if ($mediaType !== WhatsappMediaType::None && filled($mediaUrl)) {
            return $this->sendMedia($phone, $mediaType, $mediaUrl, $message, $caption);
        }

        return $this->sendText($phone, $message);
    }

    // ---------------------------------------------------------------
    // Internals
    // ---------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, data: array<string,mixed>, error: string|null}
     */
    private function request(array $payload): array
    {
        try {
            /** @var Response $response */
            $response = Http::withToken($this->session->api_token)
                ->withHeaders([
                    'X-Instance-Id' => $this->session->instance_id,
                ])
                ->timeout(30)
                ->connectTimeout(10)
                ->retry(2, 1000, throw: false)
                ->acceptJson()
                ->post(self::BASE_URL . '/whatsapp/messages/send', $payload);

            $body = $response->json() ?? [];

            if ($response->successful() && ($body['ok'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $body,
                    'error' => null,
                ];
            }

            $error = $body['message'] ?? ('HTTP ' . $response->status());

            Log::warning('MegaMsg API non-success response', [
                'instance_id' => $this->session->instance_id,
                'status' => $response->status(),
                'body' => $body,
            ]);

            return ['success' => false, 'data' => $body, 'error' => $error];

        } catch (ConnectionException $e) {
            Log::error('MegaMsg API connection error', [
                'instance_id' => $this->session->instance_id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Ensure the phone is in international E.164 format (+XXXXXXXXXXX).
     * Handles Egyptian numbers that start with 0.
     */
    private function formatPhone(string $phone): string
    {
        // Strip everything except digits and leading +
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        // Already has + prefix — return as-is
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // Strip all non-digits now
        $digits = preg_replace('/\D/', '', $cleaned);

        // Egyptian local number starting with 0 (e.g. 01012345678 → +201012345678)
        if (str_starts_with($digits, '0')) {
            return '+2' . $digits;
        }

        // Assume already has country code (e.g. 201012345678)
        return '+' . $digits;
    }
}
