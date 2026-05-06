<?php

namespace Tests\Feature;

use App\Enums\WhatsappSessionStatus;
use App\Models\WhatsappInboxMessage;
use App\Models\WhatsappSession;
use App\Services\WhatsappInboxReplyService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappInboxReplyServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_sends_direct_reply_without_campaign_and_marks_message_read(): void
    {
        Http::fake([
            'megamsg.app/*' => Http::response(['ok' => true], 200),
        ]);

        $session = $this->createWhatsappSession(WhatsappSessionStatus::Connected);
        $message = $this->createInboxMessage($session);

        $result = app(WhatsappInboxReplyService::class)->reply($message, 'أهلاً بك، كيف أقدر أساعدك؟');

        $this->assertTrue($result['success']);
        $this->assertTrue($message->fresh()->is_read);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://megamsg.app/api/whatsapp/messages/send'
            && $request['phone_number'] === '+201234567890'
            && $request['type'] === 'text'
            && $request['text'] === 'أهلاً بك، كيف أقدر أساعدك؟');
    }

    public function test_it_does_not_send_reply_when_session_is_disconnected(): void
    {
        Http::fake();

        $session = $this->createWhatsappSession(WhatsappSessionStatus::Disconnected);
        $message = $this->createInboxMessage($session);

        $result = app(WhatsappInboxReplyService::class)->reply($message, 'أهلاً بك');

        $this->assertFalse($result['success']);
        $this->assertSame('Please connect this message session before replying.', $result['error']);
        $this->assertFalse($message->fresh()->is_read);

        Http::assertNothingSent();
    }

    private function createWhatsappSession(WhatsappSessionStatus $status): WhatsappSession
    {
        return WhatsappSession::create([
            'name' => 'Main WhatsApp',
            'instance_id' => 'instance-1',
            'api_token' => 'test-token',
            'phone_number' => '+201000000000',
            'status' => $status,
            'connected_at' => $status === WhatsappSessionStatus::Connected ? now() : null,
        ]);
    }

    private function createInboxMessage(WhatsappSession $session): WhatsappInboxMessage
    {
        return WhatsappInboxMessage::create([
            'whatsapp_session_id' => $session->id,
            'message_id' => 'incoming-message-1',
            'from' => '201234567890@s.whatsapp.net',
            'push_name' => 'Ahmed',
            'text' => 'السلام عليكم',
            'message_type' => 'text',
            'is_group' => false,
            'is_read' => false,
            'raw_payload' => [],
            'received_at' => now(),
        ]);
    }
}
