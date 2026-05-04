<?php

namespace Database\Seeders;

use App\Enums\InventoryMovementType;
use App\Models\Category;
use App\Models\Color;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\SkuCode;
use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const OLD_STORAGE_BASE = 'https://backend.tijaracod.com/storage/';

    /**
     * Maps from old IDs to new IDs.
     *
     * @var array<string, array<int, int>>
     */
    private array $idMaps = [
        'categories' => [],
        'sizes' => [],
        'colors' => [],
    ];

    /**
     * Seed products, variants, SKU codes, inventory, and movements from JSON.
     */
    public function run(): void
    {
        $this->buildIdMaps();

        $json = json_decode(
            file_get_contents(database_path('seeders/prooducts.json')),
            true,
        );

        $products = $json['products'];
        $productCount = 0;
        $variantCount = 0;

        foreach ($products as $productData) {
            DB::transaction(function () use ($productData, &$productCount, &$variantCount) {
                $product = $this->seedProduct($productData);

                if ($product) {
                    $productCount++;
                    $variantCount += $this->seedVariants($product, $productData);
                }
            });
        }

        $this->command->info("Seeded {$productCount} products with {$variantCount} variants.");
    }

    /**
     * Build lookup maps from old IDs → new IDs by matching on name.
     */
    private function buildIdMaps(): void
    {
        $json = json_decode(
            file_get_contents(database_path('seeders/category.json')),
            true,
        );
        $oldCategories = $json['categories']['data'];
        $newCategories = Category::all();

        foreach ($oldCategories as $old) {
            $match = $newCategories->firstWhere('slug', $old['slug']);

            if ($match) {
                $this->idMaps['categories'][$old['id']] = $match->id;
            }
        }

        $sizesJson = json_decode(
            file_get_contents(database_path('seeders/sizes.json')),
            true,
        );
        $newSizes = Size::all();

        foreach ($sizesJson['sizes'] as $old) {
            $match = $newSizes
                ->where('name', $old['name'])
                ->where('type', $old['type'])
                ->first();

            if ($match) {
                $this->idMaps['sizes'][$old['id']] = $match->id;
            }
        }

        $colorsJson = json_decode(
            file_get_contents(database_path('seeders/colors.json')),
            true,
        );
        $newColors = Color::all();

        foreach ($colorsJson['colors'] as $old) {
            $match = $newColors->firstWhere('name', $old['name']);

            if ($match) {
                $this->idMaps['colors'][$old['id']] = $match->id;
            }
        }
    }

    /**
     * Seed a single product and its images.
     */
    private function seedProduct(array $data): ?Product
    {
        $categoryId = $this->idMaps['categories'][$data['category_id']] ?? null;

        if (! $categoryId) {
            $this->command->warn("Skipping product '{$data['name']}': category ID {$data['category_id']} not mapped.");

            return null;
        }

        $description = $data['description'] === 'undefined' || empty($data['description'])
            ? ''
            : $data['description'];

        // Download the first attachment as thumbnail and image
        $thumbnail = null;
        $image = null;

        if (! empty($data['attachments'])) {
            $firstAttachment = $data['attachments'][0];
            $thumbnail = $this->downloadImage($firstAttachment['file_path'], 'products/thumbnails');
            $image = $this->downloadImage($firstAttachment['file_path'], 'products');
        }

        // Ensure we have fallback values since these columns are NOT NULL
        $thumbnail = $thumbnail ?? 'products/thumbnails/placeholder.jpg';
        $image = $image ?? 'products/placeholder.jpg';

        $slug = $this->generateUniqueSlug($data['name']);

        $product = Product::create([
            'category_id' => $categoryId,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $description,
            'price_before_discount' => (float) $data['price'],
            'selling_price' => (float) $data['price_sell'],
            'thumbnail' => $thumbnail,
            'image' => $image,
            'has_variants' => (bool) $data['has_variants'],
            'is_active' => true,
        ]);

        // Seed additional product images (all attachments)
        foreach ($data['attachments'] ?? [] as $index => $attachment) {
            $imagePath = $this->downloadImage($attachment['file_path'], 'product-images');

            if ($imagePath) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $imagePath,
                    'sort_order' => $index,
                ]);
            }
        }

        // For products without variants, create SKU + inventory
        if (! $data['has_variants'] && ! empty($data['sku_code'])) {
            $this->seedProductSkuAndInventory($product, $data['sku_code']);
        }

        return $product;
    }

    /**
     * Seed SKU code, inventory, and movements for a product without variants.
     */
    private function seedProductSkuAndInventory(Product $product, array $skuData): void
    {
        $skuCode = SkuCode::create([
            'skuable_type' => Product::class,
            'skuable_id' => $product->id,
            'sku_code' => $skuData['sku_code'],
        ]);

        if (! empty($skuData['inventory'])) {
            $inventoryData = $skuData['inventory'];

            $inventory = Inventory::create([
                'inventoriable_type' => Product::class,
                'inventoriable_id' => $product->id,
                'quantity' => (int) $inventoryData['quantity'],
            ]);

            $this->seedMovements($inventory, $inventoryData['histories'] ?? []);
        }
    }

    /**
     * Seed variants for a product and return the count.
     */
    private function seedVariants(Product $product, array $productData): int
    {
        $variants = $productData['variants'] ?? [];

        if (empty($variants)) {
            return 0;
        }

        $count = 0;

        foreach ($variants as $variantData) {
            $sizeId = $this->idMaps['sizes'][$variantData['size_id']] ?? null;
            $colorId = $this->idMaps['colors'][$variantData['color_id']] ?? null;

            $variantImage = null;

            if (! empty($variantData['color_image'])) {
                $variantImage = $this->downloadImage($variantData['color_image'], 'variants');
            }

            $variant = Variant::create([
                'product_id' => $product->id,
                'size_id' => $sizeId,
                'color_id' => $colorId,
                'cost_price' => (float) $productData['price'],
                'price_before_discount' => (float) $productData['price'],
                'selling_price' => (float) $productData['price_sell'],
                'image' => $variantImage,
            ]);

            // Seed SKU code for this variant
            if (! empty($variantData['sku_code'])) {
                $skuData = $variantData['sku_code'];

                SkuCode::create([
                    'skuable_type' => Variant::class,
                    'skuable_id' => $variant->id,
                    'sku_code' => $skuData['sku_code'],
                ]);

                // Seed inventory for this variant
                if (! empty($skuData['inventory'])) {
                    $inventoryData = $skuData['inventory'];

                    $inventory = Inventory::create([
                        'inventoriable_type' => Variant::class,
                        'inventoriable_id' => $variant->id,
                        'quantity' => (int) $inventoryData['quantity'],
                    ]);

                    $this->seedMovements($inventory, $inventoryData['histories'] ?? []);
                }
            }

            $count++;
        }

        return $count;
    }

    /**
     * Seed inventory movements from old history records.
     */
    private function seedMovements(Inventory $inventory, array $histories): void
    {
        foreach ($histories as $history) {
            $type = $this->mapMovementType($history['change_type'], $history['action']);
            $quantity = (int) $history['quantity'];

            // In the new system: positive = addition, negative = deduction
            if ($history['change_type'] === 'out') {
                $quantity = -abs($quantity);
            }

            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'type' => $type->value,
                'quantity' => $quantity,
                'notes' => $history['reason'] ?? null,
                'created_by' => null,
            ]);
        }
    }

    /**
     * Map old change_type + action to the new InventoryMovementType enum.
     */
    private function mapMovementType(string $changeType, string $action): InventoryMovementType
    {
        if ($changeType === 'in' && $action === 'created') {
            return InventoryMovementType::Import;
        }

        if ($changeType === 'out' && $action === 'added_to_order') {
            return InventoryMovementType::Sale;
        }

        if ($changeType === 'out' && $action === 'updated') {
            return InventoryMovementType::Adjustment;
        }

        if ($changeType === 'in') {
            return InventoryMovementType::Import;
        }

        return InventoryMovementType::Adjustment;
    }

    /**
     * Generate a unique slug for the product name.
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = Str::slug(Str::transliterate($name));
        }

        if (empty($slug)) {
            $slug = 'product-' . Str::random(8);
        }

        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Download an image from the old server and store it locally.
     *
     * @return string|null The local storage path, or null on failure.
     */
    private function downloadImage(string $oldPath, string $directory): ?string
    {
        $url = self::OLD_STORAGE_BASE . $oldPath;
        $filename = basename($oldPath);
        $localPath = $directory . '/' . $filename;

        // Skip if already downloaded (for de-duplication across variants sharing images)
        if (Storage::disk('public')->exists($localPath)) {
            return $localPath;
        }

        try {
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($localPath, $response->body());

                return $localPath;
            }

            $this->command->warn("HTTP {$response->status()} downloading: {$url}");
        } catch (\Exception $e) {
            $this->command->warn("Failed to download: {$url}");
        }

        return null;
    }
}
