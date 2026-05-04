<?php

namespace App\Filament\Admin\Resources\WhatsappCampaigns;

use App\Enums\WhatsappCampaignStatus;
use App\Filament\Admin\Resources\WhatsappCampaigns\Pages\CreateWhatsappCampaign;
use App\Filament\Admin\Resources\WhatsappCampaigns\Pages\EditWhatsappCampaign;
use App\Filament\Admin\Resources\WhatsappCampaigns\Pages\ListWhatsappCampaigns;
use App\Filament\Admin\Resources\WhatsappCampaigns\Pages\ViewWhatsappCampaign;
use App\Filament\Admin\Resources\WhatsappCampaigns\Schemas\WhatsappCampaignForm;
use App\Filament\Admin\Resources\WhatsappCampaigns\Tables\WhatsappCampaignsTable;
use App\Jobs\SendWhatsappCampaignJob;
use App\Models\WhatsappCampaign;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsappCampaignResource extends Resource
{
    protected static ?string $model = WhatsappCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('WhatsApp Campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('WhatsApp Campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('WhatsApp Campaigns');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('WhatsApp');
    }

    public static function form(Schema $schema): Schema
    {
        return WhatsappCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsappCampaignsTable::configure($table)
            ->actions([
                Action::make('launch')
                    ->label(__('Launch'))
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Launch Campaign?'))
                    ->modalDescription(__('This will start sending messages to all pending contacts. Make sure your session is connected on MegaMsg.'))
                    ->visible(fn (WhatsappCampaign $record) => in_array(
                        $record->status,
                        [WhatsappCampaignStatus::Draft, WhatsappCampaignStatus::Scheduled]
                    ))
                    ->action(function (WhatsappCampaign $record) {
                        if (! $record->session->isConnected()) {
                            Notification::make()
                                ->title(__('Session is not connected.'))
                                ->body(__('Please mark the session as Connected first.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($record->contacts()->where('status', 'pending')->doesntExist()) {
                            Notification::make()
                                ->title(__('No pending contacts found.'))
                                ->warning()
                                ->send();

                            return;
                        }

                        SendWhatsappCampaignJob::dispatch($record);

                        Notification::make()
                            ->title(__('Campaign queued successfully!'))
                            ->body(__('Sending to :count contacts.', ['count' => $record->contacts()->count()]))
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (WhatsappCampaign $record) => in_array(
                        $record->status,
                        [WhatsappCampaignStatus::Draft, WhatsappCampaignStatus::Scheduled]
                    )),
                DeleteAction::make()
                    ->visible(fn (WhatsappCampaign $record) => ! in_array(
                        $record->status,
                        [WhatsappCampaignStatus::Running]
                    )),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsappCampaigns::route('/'),
            'create' => CreateWhatsappCampaign::route('/create'),
            'edit' => EditWhatsappCampaign::route('/{record}/edit'),
            'view' => ViewWhatsappCampaign::route('/{record}'),
        ];
    }
}
