<?php

namespace App\Filament\Admin\Resources\WhatsappCampaigns\Pages;

use App\Enums\WhatsappCampaignStatus;
use App\Filament\Admin\Resources\WhatsappCampaigns\WhatsappCampaignResource;
use App\Jobs\SendWhatsappCampaignJob;
use App\Models\WhatsappCampaign;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewWhatsappCampaign extends ViewRecord
{
    protected static string $resource = WhatsappCampaignResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Campaign Overview'))
                ->icon('heroicon-o-megaphone')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label(__('Campaign Name'))
                        ->columnSpan(2),

                    TextEntry::make('status')
                        ->label(__('Status'))
                        ->badge()
                        ->color(fn (WhatsappCampaignStatus $state) => $state->color())
                        ->formatStateUsing(fn (WhatsappCampaignStatus $state) => $state->label()),

                    TextEntry::make('session.name')
                        ->label(__('Session')),

                    TextEntry::make('delay_seconds')
                        ->label(__('Delay Between Messages'))
                        ->suffix(' seconds'),

                    TextEntry::make('scheduled_at')
                        ->label(__('Scheduled At'))
                        ->dateTime()
                        ->placeholder(__('Manual launch')),

                    TextEntry::make('message')
                        ->label(__('Message Text'))
                        ->columnSpanFull()
                        ->prose(),
                ]),

            Section::make(__('Progress'))
                ->icon('heroicon-o-chart-bar')
                ->columns(3)
                ->schema([
                    TextEntry::make('total_contacts')
                        ->label(__('Total Contacts'))
                        ->numeric(),

                    TextEntry::make('sent_count')
                        ->label(__('Sent'))
                        ->numeric()
                        ->color('success'),

                    TextEntry::make('failed_count')
                        ->label(__('Failed'))
                        ->numeric()
                        ->color('danger'),

                    TextEntry::make('started_at')
                        ->label(__('Started At'))
                        ->dateTime()
                        ->placeholder('—'),

                    TextEntry::make('completed_at')
                        ->label(__('Completed At'))
                        ->dateTime()
                        ->placeholder('—'),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('launch')
                ->label(__('Launch Campaign'))
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => in_array(
                    $this->record->status,
                    [WhatsappCampaignStatus::Draft, WhatsappCampaignStatus::Scheduled]
                ))
                ->action(function () {
                    /** @var WhatsappCampaign $campaign */
                    $campaign = $this->record;

                    if (! $campaign->session->isConnected()) {
                            Notification::make()
                                ->title(__('Session is not connected.'))
                                ->body(__('Please mark the session as Connected first.'))
                                ->danger()
                                ->send();

                        return;
                    }

                    SendWhatsappCampaignJob::dispatch($campaign);

                    Notification::make()
                        ->title(__('Campaign launched!'))
                        ->body("Sending to {$campaign->total_contacts} contacts.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'sent_count', 'failed_count']);
                }),

            EditAction::make()
                ->visible(fn () => in_array(
                    $this->record->status,
                    [WhatsappCampaignStatus::Draft, WhatsappCampaignStatus::Scheduled]
                )),

            DeleteAction::make()
                ->visible(fn () => $this->record->status !== WhatsappCampaignStatus::Running),
        ];
    }
}
