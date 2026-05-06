<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('tabs.all'))
                ->badge(fn () => $this->getModel()::count())
                ->badgeColor('primary'),

            'new_order' => Tab::make(__('tabs.new_order'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatusEnum::NEW_ORDER->value))
                ->badgeColor('success')
                ->badge(fn () => $this->getModel()::where('status', OrderStatusEnum::NEW_ORDER->value)->count()),

            'confirmed' => Tab::make(__('tabs.confirmed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatusEnum::CONFIRMED->value)->whereNull('delivery_status'))
                ->badge(fn () => $this->getModel()::where('status', OrderStatusEnum::CONFIRMED->value)->whereNull('delivery_status')->count())
                ->badgeColor('warning'),

            'all_orders_sent' => Tab::make(__('tabs.all_orders_sent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatusEnum::CONFIRMED->value)->whereNotNull('delivery_status'))
                ->badge(fn () => $this->getModel()::where('status', OrderStatusEnum::CONFIRMED->value)->whereNotNull('delivery_status')->count())
                ->badgeColor('info'),

            'follow_up' => Tab::make(__('tabs.follow_up'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    OrderStatusEnum::RAPPEL->value,
                    OrderStatusEnum::REPORTER->value,
                    OrderStatusEnum::OCCUPE->value,
                    OrderStatusEnum::PAS_REPONSE_1->value,
                    OrderStatusEnum::PAS_REPONSE_2->value,
                    OrderStatusEnum::PAS_REPONSE_3_SMS->value,
                    OrderStatusEnum::NUMERO_INCORRECT->value,
                    OrderStatusEnum::ANNULE->value,
                    OrderStatusEnum::FAKE->value,
                    OrderStatusEnum::DOUBLE->value,
                ]))
                ->badge(fn () => $this->getModel()::whereIn('status', [
                    OrderStatusEnum::RAPPEL->value,
                    OrderStatusEnum::REPORTER->value,
                    OrderStatusEnum::OCCUPE->value,
                    OrderStatusEnum::PAS_REPONSE_1->value,
                    OrderStatusEnum::PAS_REPONSE_2->value,
                    OrderStatusEnum::PAS_REPONSE_3_SMS->value,
                    OrderStatusEnum::NUMERO_INCORRECT->value,
                    OrderStatusEnum::ANNULE->value,
                    OrderStatusEnum::FAKE->value,
                    OrderStatusEnum::DOUBLE->value,
                ])->count())
                ->badgeColor('danger'),
        ];

    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
