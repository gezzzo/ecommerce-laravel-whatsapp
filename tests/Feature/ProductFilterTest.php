<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_category_page_applies_price_filters(): void
    {
        $category = $this->createCategory('عطور اماراتية');

        $this->createProduct($category, 'عطر اقتصادي', 120);
        $this->createProduct($category, 'عطر فاخر', 350);

        $this->get(route('category', $category->slug).'?min_price=200')
            ->assertOk()
            ->assertSee('عطر فاخر')
            ->assertDontSee('عطر اقتصادي')
            ->assertSee('تطبيق الفلاتر');
    }

    public function test_products_page_applies_category_filters(): void
    {
        $perfumes = $this->createCategory('عطور');
        $clothes = $this->createCategory('ملابس');

        $this->createProduct($perfumes, 'عطر ورد', 220);
        $this->createProduct($clothes, 'عباية سوداء', 300);

        $this->get(route('products', ['categories' => [$clothes->id]]))
            ->assertOk()
            ->assertSee('عباية سوداء')
            ->assertDontSee('عطر ورد');
    }

    public function test_filters_keep_sorting_working(): void
    {
        $category = $this->createCategory('منتجات');

        $this->createProduct($category, 'منتج غالي', 400);
        $this->createProduct($category, 'منتج رخيص', 100);

        $this->get(route('products', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['منتج رخيص', 'منتج غالي']);
    }

    private function createCategory(string $name): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(8),
        ]);
    }

    private function createProduct(Category $category, string $name, int $price): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(8),
            'description' => 'Test product',
            'selling_price' => $price,
            'thumbnail' => 'products/thumb.jpg',
            'image' => 'products/image.jpg',
            'has_variants' => false,
            'is_active' => true,
        ]);
    }
}
