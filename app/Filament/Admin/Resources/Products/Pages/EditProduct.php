<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\SkuCode;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $product = $this->record;

        // Ensure product has a SKU code
        if (! $product->skuCode) {
            $productSku = $this->generateSkuCode('PRD', $product->id);
            $product->skuCode()->create(['sku_code' => $productSku]);
        }

        if ($product->has_variants) {
            // Generate SKU codes and inventory for variants that don't have them
            $product->load('variants.color', 'variants.size', 'variants.skuCode', 'variants.inventory');
            foreach ($product->variants as $variant) {
                if (! $variant->skuCode) {
                    $colorCode = $variant->color
                        ? mb_strtoupper(mb_substr($variant->color->name, 0, 3))
                        : 'NOC';
                    $sizeCode = $variant->size
                        ? mb_strtoupper(mb_substr($variant->size->name, 0, 3))
                        : 'NOS';
                    $prefix = "VAR-{$product->id}-{$colorCode}-{$sizeCode}";
                    $variantSku = $this->generateSkuCode($prefix, $variant->id);
                    $variant->skuCode()->create(['sku_code' => $variantSku]);
                }

                if (! $variant->inventory) {
                    $variant->inventory()->create(['quantity' => 0]);
                }
            }
        } else {
            // Ensure product has inventory when no variants
            if (! $product->inventory) {
                $product->inventory()->create(['quantity' => 0]);
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
