<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Size;
use App\Models\SkuCode;
use App\Models\Variant;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_add_product_to_wishlist_from_heart_button(): void
    {
        [$product, $skuCode] = $this->createProductWithSku();

        $response = $this
            ->from(route('products'))
            ->post(route('wishlist.add'), [
                'product_id' => $product->id,
            ]);

        $response->assertRedirect(route('products'));
        $response->assertSessionHas('success', 'تمت الإضافة إلى المفضلة.');
        $response->assertSessionHas('wishlist_count', 1);

        $this->assertDatabaseHas('wishlist_items', [
            'sku_code' => $skuCode->id,
        ]);
    }

    public function test_heart_button_toggles_existing_wishlist_item(): void
    {
        [$product, $skuCode] = $this->createProductWithSku();

        $this->post(route('wishlist.add'), [
            'product_id' => $product->id,
        ]);

        $response = $this->post(route('wishlist.add'), [
            'product_id' => $product->id,
        ]);

        $response->assertSessionHas('success', 'تم حذف المنتج من المفضلة.');
        $response->assertSessionHas('wishlist_count', 0);

        $this->assertDatabaseMissing('wishlist_items', [
            'sku_code' => $skuCode->id,
        ]);
    }

    public function test_wishlist_page_shows_saved_products(): void
    {
        [$product, $skuCode] = $this->createProductWithSku();

        $wishlist = Wishlist::create([
            'session_id' => Str::uuid()->toString(),
        ]);

        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'sku_code' => $skuCode->id,
        ]);

        $response = $this
            ->withSession(['wishlist_id' => $wishlist->id])
            ->get(route('wishlist'));

        $response
            ->assertOk()
            ->assertSee('المفضلة')
            ->assertSee($product->name)
            ->assertSee('إضافة للسلة');
    }

    public function test_product_can_be_removed_from_wishlist_page(): void
    {
        [, $skuCode] = $this->createProductWithSku();

        $wishlist = Wishlist::create([
            'session_id' => Str::uuid()->toString(),
        ]);

        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'sku_code' => $skuCode->id,
        ]);

        $response = $this
            ->withSession(['wishlist_id' => $wishlist->id])
            ->delete(route('wishlist.remove', $skuCode));

        $response->assertRedirect();
        $response->assertSessionHas('wishlist_count', 0);

        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'sku_code' => $skuCode->id,
        ]);
    }

    public function test_selected_variant_can_be_added_to_wishlist(): void
    {
        [$product, $variant, $skuCode] = $this->createVariantWithSku();

        $this->post(route('wishlist.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'sku_code' => $skuCode->id,
        ]);
    }

    public function test_variant_product_requires_selected_variant_before_wishlist_add(): void
    {
        [$product, $variant, $skuCode] = $this->createVariantWithSku();

        $response = $this
            ->from(route('product', $product->slug))
            ->post(route('wishlist.add'), [
                'product_id' => $product->id,
            ]);

        $response->assertRedirect(route('product', $product->slug));
        $response->assertSessionHas('error', 'يرجى اختيار اللون/المقاس أولاً.');

        $this->assertDatabaseMissing('wishlist_items', [
            'sku_code' => $skuCode->id,
        ]);
    }

    public function test_adding_wishlist_item_to_cart_removes_it_from_wishlist(): void
    {
        [$product, $skuCode] = $this->createProductWithSku();

        $wishlist = Wishlist::create([
            'session_id' => Str::uuid()->toString(),
        ]);

        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'sku_code' => $skuCode->id,
        ]);

        $response = $this
            ->withSession(['wishlist_id' => $wishlist->id])
            ->from(route('wishlist'))
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $response->assertRedirect(route('wishlist'));
        $response->assertSessionHas('success', 'تمت الإضافة إلى السلة بنجاح!');
        $response->assertSessionHas('wishlist_count', 0);
        $response->assertSessionHas('wishlist_sku_codes', []);
        $response->assertSessionHas('cart_count', 1);

        $this->assertDatabaseHas('cart_items', [
            'sku_code' => $skuCode->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'sku_code' => $skuCode->id,
        ]);
    }

    /**
     * @return array{Product, SkuCode}
     */
    private function createProductWithSku(): array
    {
        $category = $this->createCategory();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'عباية اختبار',
            'slug' => 'wishlist-product-'.Str::random(8),
            'description' => 'Test product',
            'selling_price' => 150,
            'thumbnail' => 'products/thumb.jpg',
            'image' => 'products/image.jpg',
            'has_variants' => false,
            'is_active' => true,
        ]);

        Inventory::create([
            'inventoriable_type' => Product::class,
            'inventoriable_id' => $product->id,
            'quantity' => 5,
        ]);

        $skuCode = SkuCode::create([
            'skuable_type' => Product::class,
            'skuable_id' => $product->id,
            'sku_code' => 'SKU-'.Str::upper(Str::random(8)),
        ]);

        return [$product, $skuCode];
    }

    /**
     * @return array{Product, Variant, SkuCode}
     */
    private function createVariantWithSku(): array
    {
        $category = $this->createCategory();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'عباية خيارات',
            'slug' => 'wishlist-variant-product-'.Str::random(8),
            'description' => 'Test variant product',
            'selling_price' => 200,
            'thumbnail' => 'products/thumb.jpg',
            'image' => 'products/image.jpg',
            'has_variants' => true,
            'is_active' => true,
        ]);

        $size = Size::create([
            'name' => 'XL',
            'type' => 'clothes',
        ]);

        $color = Color::create([
            'name' => 'أسود',
            'hex_code' => '#000000',
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size_id' => $size->id,
            'color_id' => $color->id,
            'cost_price' => 100,
            'price_before_discount' => 250,
            'selling_price' => 200,
            'image' => 'variants/black.jpg',
        ]);

        Inventory::create([
            'inventoriable_type' => Variant::class,
            'inventoriable_id' => $variant->id,
            'quantity' => 3,
        ]);

        $skuCode = SkuCode::create([
            'skuable_type' => Variant::class,
            'skuable_id' => $variant->id,
            'sku_code' => 'SKU-'.Str::upper(Str::random(8)),
        ]);

        return [$product, $variant, $skuCode];
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'ملابس المحجبات',
            'slug' => 'category-'.Str::random(8),
        ]);
    }
}
