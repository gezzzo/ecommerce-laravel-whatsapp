<?php

namespace App\Jobs;

use App\Enums\WhatsappCampaignStatus;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignContact;
use App\Models\WhatsappCampaignLog;
use App\Services\WhatsappService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWhatsappCampaignContact implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Maximum attempts before marking as failed. */
    public int $tries = 2;

    /** @var int Timeout in seconds per attempt. */
    public int $timeout = 60;

    public function __construct(
        public readonly WhatsappCampaignContact $contact
    ) {}

    public function handle(): void
    {
        // Abort if the batch was cancelled
        if ($this->batch()?->cancelled()) {
            return;
        }

        $campaign = $this->contact->campaign()->with('session')->firstOrFail();

        // Skip already-processed contacts
        if ($this->contact->status !== 'pending') {
            return;
        }

        $service = new WhatsappService($campaign->session);

        $result = $service->sendCampaignMessage(
            $this->contact->phone,
            $campaign->message,
            $campaign->media_type,
            $campaign->media_url,
            $campaign->media_caption ?? ''
        );

        if ($result['success']) {
            $this->contact->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            $campaign->increment('sent_count');

            WhatsappCampaignLog::create([
                'whatsapp_campaign_id' => $campaign->id,
                'whatsapp_campaign_contact_id' => $this->contact->id,
                'phone' => $this->contact->phone,
                'event' => 'sent',
                'message' => 'Message sent successfully.',
                'payload' => $result['data'] ?? null,
                'happened_at' => now(),
            ]);
        } else {
            $this->contact->update([
                'status' => 'failed',
                'error_message' => $result['error'],
            ]);

            $campaign->increment('failed_count');

            WhatsappCampaignLog::create([
                'whatsapp_campaign_id' => $campaign->id,
                'whatsapp_campaign_contact_id' => $this->contact->id,
                'phone' => $this->contact->phone,
                'event' => 'failed',
                'message' => $result['error'],
                'happened_at' => now(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->contact->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        $campaign = $this->contact->campaign;
        if ($campaign) {
            $campaign->increment('failed_count');

            WhatsappCampaignLog::create([
                'whatsapp_campaign_id' => $campaign->id,
                'whatsapp_campaign_contact_id' => $this->contact->id,
                'phone' => $this->contact->phone,
                'event' => 'failed',
                'message' => 'Job failed: ' . $exception->getMessage(),
                'happened_at' => now(),
            ]);
        }
    }
}
