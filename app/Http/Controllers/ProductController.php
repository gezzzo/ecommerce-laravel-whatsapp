<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true);

        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->input('categories'));
        }

        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->input('max_price'));
        }

        $query = match ($request->input('sort')) {
            'newest'     => $query->latest(),
            'price_asc'  => $query->orderBy('selling_price'),
            'price_desc' => $query->orderByDesc('selling_price'),
            default      => $query->latest('id'),
        };

        $products = $query->paginate(20);

        $sidebarCategories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

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

        $products = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->paginate(20);

        $sidebarCategories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('products.index', [
            'products' => $products,
            'sidebarCategories' => $sidebarCategories,
            'searchQuery' => $query,
        ]);
    }

    public function offers(): View
    {
        $products = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true)
            ->whereNotNull('price_before_discount')
            ->whereColumn('price_before_discount', '>', 'selling_price')
            ->latest()
            ->paginate(20);

        $sidebarCategories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

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

    public function category(string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $products = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true)
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(20);

        $sidebarCategories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('products.index', [
            'products' => $products,
            'sidebarCategories' => $sidebarCategories,
            'category' => $category,
        ]);
    }
}
