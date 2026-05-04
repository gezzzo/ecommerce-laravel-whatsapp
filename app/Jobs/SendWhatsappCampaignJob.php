<?php

namespace App\Jobs;

use App\Enums\WhatsappCampaignStatus;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignLog;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SendWhatsappCampaignJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Unique lock per campaign. */
    public int $uniqueFor = 3600;

    public function __construct(public readonly WhatsappCampaign $campaign) {}

    /** Unique key prevents duplicate dispatches for the same campaign. */
    public function uniqueId(): int
    {
        return $this->campaign->id;
    }

    public function handle(): void
    {
        $campaign = $this->campaign->fresh();

        if (! $campaign || ! in_array($campaign->status, [WhatsappCampaignStatus::Scheduled, WhatsappCampaignStatus::Draft])) {
            return;
        }

        // Ensure session is connected
        if (! $campaign->session->isConnected()) {
            $campaign->update(['status' => WhatsappCampaignStatus::Failed]);

            WhatsappCampaignLog::create([
                'whatsapp_campaign_id' => $campaign->id,
                'event' => 'failed',
                'message' => 'Session is not connected.',
                'happened_at' => now(),
            ]);

            return;
        }

        $contacts = $campaign->contacts()->pending()->orderBy('sort_order')->get();

        if ($contacts->isEmpty()) {
            $campaign->update([
                'status' => WhatsappCampaignStatus::Completed,
                'completed_at' => now(),
            ]);

            return;
        }

        $campaign->update([
            'status' => WhatsappCampaignStatus::Running,
            'started_at' => now(),
            'total_contacts' => $campaign->contacts()->count(),
        ]);

        WhatsappCampaignLog::create([
            'whatsapp_campaign_id' => $campaign->id,
            'event' => 'info',
            'message' => "Campaign started — {$contacts->count()} contacts to process.",
            'happened_at' => now(),
        ]);

        // Build jobs with delay between each
        $jobs = $contacts->map(function ($contact, int $index) use ($campaign) {
            return (new ProcessWhatsappCampaignContact($contact))
                ->delay(now()->addSeconds($index * $campaign->delay_seconds));
        })->all();

        Bus::batch($jobs)
            ->name("Campaign #{$campaign->id}: {$campaign->name}")
            ->allowFailures()
            ->finally(function (Batch $batch) use ($campaign) {
                $fresh = $campaign->fresh();
                if (! $fresh) {
                    return;
                }

                $newStatus = $batch->hasFailures()
                    ? WhatsappCampaignStatus::Completed  // completed but with some failures
                    : WhatsappCampaignStatus::Completed;

                $fresh->update([
                    'status' => $newStatus,
                    'completed_at' => now(),
                ]);

                WhatsappCampaignLog::create([
                    'whatsapp_campaign_id' => $fresh->id,
                    'event' => 'info',
                    'message' => "Campaign completed. Sent: {$fresh->sent_count} | Failed: {$fresh->failed_count}",
                    'happened_at' => now(),
                ]);
            })
            ->dispatch();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsappCampaignJob failed', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);

        $this->campaign->update(['status' => WhatsappCampaignStatus::Failed]);
    }
}
