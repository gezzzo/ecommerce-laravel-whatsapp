<?php

namespace App\Filament\Admin\Resources\Inventories\Tables;

use App\Enums\InventoryMovementType;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Variant;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('inventoriable_type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\\Models\\Product' => __('Product'),
                        'App\\Models\\Variant' => __('Variant'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'App\\Models\\Product' => 'primary',
                        'App\\Models\\Variant' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('inventoriable')
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
                    })
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHasMorph('inventoriable', [Product::class], function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })->orWhereHasMorph('inventoriable', [Variant::class], function ($q) use ($search) {
                                $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                            });
                        });
                    }),
                TextColumn::make('quantity')
                    ->label(__('Current Stock'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('movements_count')
                    ->counts('movements')
                    ->label(__('Movements'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('inventoriable_type')
                    ->label(__('Type'))
                    ->options([
                        'App\\Models\\Product' => __('Product'),
                        'App\\Models\\Variant' => __('Variant'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('add_stock')
                    ->label(__('Import'))
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
                    ->action(function (Inventory $record, array $data): void {
                        $record->movements()->create([
                            'type' => $data['type'],
                            'quantity' => abs((int) $data['quantity']),
                            'notes' => $data['notes'] ?? null,
                            'created_by' => Auth::id(),
                        ]);

                        $record->recalculateQuantity();

                        Notification::make()
                            ->success()
                            ->title(__('Stock imported successfully'))
                            ->body("Added {$data['quantity']} units")
                            ->send();
                    }),
                Action::make('deduct_stock')
                    ->label(__('Deduct'))
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
                    ->action(function (Inventory $record, array $data): void {
                        $deductQty = abs((int) $data['quantity']);

                        if ($record->quantity < $deductQty) {
                            Notification::make()
                                ->danger()
                                ->title(__('Insufficient stock'))
                                ->body("Current stock is {$record->quantity}, cannot deduct {$deductQty}")
                                ->send();

                            return;
                        }

                        $record->movements()->create([
                            'type' => $data['type'],
                            'quantity' => -$deductQty,
                            'notes' => $data['notes'] ?? null,
                            'created_by' => Auth::id(),
                        ]);

                        $record->recalculateQuantity();

                        Notification::make()
                            ->success()
                            ->title(__('Stock deducted successfully'))
                            ->body("Removed {$deductQty} units")
                            ->send();
                    }),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
