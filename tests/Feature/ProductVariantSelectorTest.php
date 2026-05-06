<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\Variant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductVariantSelectorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_page_previews_all_colors_before_size_selection(): void
    {
        $category = Category::create([
            'name' => 'ملابس',
            'slug' => 'clothes',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'عباية اختبار',
            'slug' => 'test-abaya',
            'description' => 'وصف المنتج',
            'selling_price' => 299,
            'thumbnail' => 'products/test.webp',
            'image' => 'products/test.webp',
            'has_variants' => true,
            'is_active' => true,
        ]);

        $medium = Size::create(['name' => 'M', 'type' => 'clothes']);
        $large = Size::create(['name' => 'L', 'type' => 'clothes']);
        $red = Color::create(['name' => 'أحمر', 'hex_code' => '#ff0000']);
        $blue = Color::create(['name' => 'أزرق', 'hex_code' => '#0000ff']);

        $this->createVariant($product, $medium, $red);
        $this->createVariant($product, $medium, $blue);
        $this->createVariant($product, $large, $red);

        $this->get(route('product', $product->slug))
            ->assertOk()
            ->assertSee('هذه كل ألوان المنتج. اختر المقاس لتفعيل الألوان المتاحة له.')
            ->assertSee('أحمر - اختر المقاس أولاً', false)
            ->assertSee('أزرق - اختر المقاس أولاً', false)
            ->assertSee('اختر المقاس أولاً')
            ->assertSee('id="mobileAddToCartBtn"', false)
            ->assertSee('form="addToCartForm"', false)
            ->assertSee('id="mobileAddToCartText"', false);
    }

    private function createVariant(Product $product, Size $size, Color $color): void
    {
        $variant = Variant::create([
            'product_id' => $product->id,
            'size_id' => $size->id,
            'color_id' => $color->id,
            'cost_price' => 0,
            'price_before_discount' => 0,
            'selling_price' => 299,
        ]);

        $variant->inventory()->create(['quantity' => 5]);
    }
}
