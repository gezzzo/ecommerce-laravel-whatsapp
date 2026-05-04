<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Enums\InventoryMovementType;
use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\SkuCode;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $product = $this->record;
        $formData = $this->data;

        // Auto-generate SKU code for the product
        $productSku = $this->generateSkuCode('PRD', $product->id);
        $product->skuCode()->create(['sku_code' => $productSku]);

        if ($product->has_variants) {
            // Product has variants → inventory tracked at variant level
            $product->load('variants.color', 'variants.size');

            // Get the variant form data to read initial_quantity values
            $variantFormData = $formData['variants'] ?? [];

            foreach ($product->variants as $index => $variant) {
                $colorCode = $variant->color
                    ? mb_strtoupper(mb_substr($variant->color->name, 0, 3))
                    : 'NOC';
                $sizeCode = $variant->size
                    ? mb_strtoupper(mb_substr($variant->size->name, 0, 3))
                    : 'NOS';
                $prefix = "VAR-{$product->id}-{$colorCode}-{$sizeCode}";
                $variantSku = $this->generateSkuCode($prefix, $variant->id);
                $variant->skuCode()->create(['sku_code' => $variantSku]);

                // Get initial quantity from the form (matched by index)
                $formEntry = array_values($variantFormData)[$index] ?? [];
                $initialQty = (int) ($formEntry['initial_quantity'] ?? 0);

                // Create inventory record with the initial quantity
                $inventory = $variant->inventory()->create(['quantity' => $initialQty]);

                // Log the initial import movement if qty > 0
                if ($initialQty > 0) {
                    $inventory->movements()->create([
                        'type' => InventoryMovementType::Import,
                        'quantity' => $initialQty,
                        'notes' => 'Initial stock on product creation',
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        } else {
            // Product without variants → inventory tracked at product level
            $initialQty = (int) ($formData['initial_quantity'] ?? 0);
            $inventory = $product->inventory()->create(['quantity' => $initialQty]);

            // Log the initial import movement if qty > 0
            if ($initialQty > 0) {
                $inventory->movements()->create([
                    'type' => InventoryMovementType::Import,
                    'quantity' => $initialQty,
                    'notes' => 'Initial stock on product creation',
                    'created_by' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Generate a unique SKU code with a given prefix and ID.
     */
    private function generateSkuCode(string $prefix, int $id): string
    {
        $base = $prefix . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);

        $counter = 0;
        $sku = $base;
        while (SkuCode::where('sku_code', $sku)->exists()) {
            $counter++;
            $sku = $base . '-' . $counter;
        }

        return $sku;
    }
}
