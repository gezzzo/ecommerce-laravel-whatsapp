<?php

namespace App\Filament\Admin\Resources\Inventories\Pages;

use App\Enums\InventoryMovementType;
use App\Filament\Admin\Resources\Inventories\InventoryResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Variant;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('inventoriable_type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\\Models\\Product' => __('Product'),
                        'App\\Models\\Variant' => __('Variant'),
                        default => $state,
                    })
                    ->badge(),
                TextEntry::make('inventoriable')
                    ->label(__('Item'))
                    ->formatStateUsing(function ($state, Inventory $record): string {
                        $model = $record->inventoriable;
                        if ($model instanceof Product) {
                            return $model->name;
                        }
                        if ($model instanceof Variant) {
                            $parts = [$model->product?->name ?? 'N/A'];
                            if ($model->color) {
                                $parts[] = $model->color->name;
                            }
                            if ($model->size) {
                                $parts[] = $model->size->name;
                            }

                            return implode(' / ', $parts);
                        }

                        return 'N/A';
                    }),
                TextEntry::make('quantity')
                    ->label(__('Current Stock'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                TextEntry::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_stock')
                ->label(__('Import Stock'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Select::make('type')
                        ->label(__('Movement Type'))
                        ->options([
                            InventoryMovementType::Import->value => InventoryMovementType::Import->label(),
                            InventoryMovementType::Return->value => InventoryMovementType::Return->label(),
                            InventoryMovementType::Adjustment->value => InventoryMovementType::Adjustment->label(),
                        ])
                        ->required()
                        ->default(InventoryMovementType::Import->value),
                    TextInput::make('quantity')
                        ->label(__('Quantity'))
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    Textarea::make('notes')
                        ->label(__('Notes')),
                ])
                ->action(function (array $data): void {
                    $this->record->movements()->create([
                        'type' => $data['type'],
                        'quantity' => abs((int) $data['quantity']),
                        'notes' => $data['notes'] ?? null,
                        'created_by' => Auth::id(),
                    ]);

                    $this->record->recalculateQuantity();

                    Notification::make()
                        ->success()
                        ->title(__('Stock imported successfully'))
                        ->body("Added {$data['quantity']} units")
                        ->send();
                }),
            Action::make('deduct_stock')
                ->label(__('Deduct Stock'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger')
                ->form([
                    Select::make('type')
                        ->label(__('Movement Type'))
                        ->options([
                            InventoryMovementType::Sale->value => InventoryMovementType::Sale->label(),
                            InventoryMovementType::Adjustment->value => InventoryMovementType::Adjustment->label(),
                        ])
                        ->required()
                        ->default(InventoryMovementType::Adjustment->value),
                    TextInput::make('quantity')
                        ->label(__('Quantity'))
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    Textarea::make('notes')
                        ->label(__('Notes')),
                ])
                ->action(function (array $data): void {
                    $deductQty = abs((int) $data['quantity']);

                    if ($this->record->quantity < $deductQty) {
                        Notification::make()
                            ->danger()
                            ->title(__('Insufficient stock'))
                            ->body("Current stock is {$this->record->quantity}, cannot deduct {$deductQty}")
                            ->send();

                        return;
                    }

                    $this->record->movements()->create([
                        'type' => $data['type'],
                        'quantity' => -$deductQty,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => Auth::id(),
                    ]);

                    $this->record->recalculateQuantity();

                    Notification::make()
                        ->success()
                        ->title(__('Stock deducted successfully'))
                        ->body("Removed {$deductQty} units")
                        ->send();
                }),
        ];
    }
}
