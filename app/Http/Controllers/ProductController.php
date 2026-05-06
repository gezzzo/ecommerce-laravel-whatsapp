<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = $this->applyFilters($this->productsQuery(), $request)
            ->paginate(20);

        $sidebarCategories = $this->sidebarCategories();

        return view('products.index', compact('products', 'sidebarCategories'));
    }

    public function show(string $slug): View
    {
        $product = Product::with([
            'category',
            'images',
            'inventory',
            'variants.size',
            'variants.color',
            'variants.inventory',
            'variants.skuCode',
            'skuCode',
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedProducts = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(5)
            ->inRandomOrder()
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function search(Request $request): View
    {
        $query = $request->input('q', '');

        $products = $this->applyFilters(
            $this->productsQuery()
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                }),
            $request
        )
            ->paginate(20);

        $sidebarCategories = $this->sidebarCategories();

        return view('products.index', [
            'products' => $products,
            'sidebarCategories' => $sidebarCategories,
            'searchQuery' => $query,
        ]);
    }

    public function offers(Request $request): View
    {
        $products = $this->applyFilters(
            $this->productsQuery()
                ->whereNotNull('price_before_discount')
                ->whereColumn('price_before_discount', '>', 'selling_price'),
            $request
        )
            ->paginate(20);

        $sidebarCategories = $this->sidebarCategories();

        return view('products.index', [
            'products' => $products,
            'sidebarCategories' => $sidebarCategories,
            'pageTitle' => 'العروض والخصومات',
        ]);
    }

    public function categories(): View
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('categories'));
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $products = $this->applyFilters($this->productsQuery(), $request, $category->id)
            ->paginate(20);

        $sidebarCategories = $this->sidebarCategories();

        return view('products.index', [
            'products' => $products,
            'sidebarCategories' => $sidebarCategories,
            'category' => $category,
        ]);
    }

    private function productsQuery(): Builder
    {
        return Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true);
    }

    private function applyFilters(Builder $query, Request $request, ?int $fallbackCategoryId = null): Builder
    {
        $categoryIds = collect((array) $request->input('categories', []))
            ->filter(fn ($categoryId): bool => is_numeric($categoryId))
            ->map(fn ($categoryId): int => (int) $categoryId)
            ->values()
            ->all();

        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        } elseif ($fallbackCategoryId) {
            $query->where('category_id', $fallbackCategoryId);
        }

        if (is_numeric($request->input('min_price'))) {
            $query->where('selling_price', '>=', (float) $request->input('min_price'));
        }

        if (is_numeric($request->input('max_price'))) {
            $query->where('selling_price', '<=', (float) $request->input('max_price'));
        }

        return match ($request->input('sort')) {
            'newest' => $query->latest(),
            'price_asc' => $query->orderBy('selling_price'),
            'price_desc' => $query->orderByDesc('selling_price'),
            default => $query->latest('id'),
        };
    }

    private function sidebarCategories(): Collection
    {
        return Category::withCount([
            'products' => fn (Builder $query) => $query->where('is_active', true),
        ])
            ->orderBy('sort_order')
            ->get();
    }
}
