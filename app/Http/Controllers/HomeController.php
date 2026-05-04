<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        $featuredProducts = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->limit(10)
            ->get();

        $newArrivals = Product::with(['category', 'inventory', 'skuCode', 'variants.skuCode'])
            ->where('is_active', true)
            ->latest()
            ->limit(5)
            ->get();

        return view('home', compact('categories', 'featuredProducts', 'newArrivals'));
    }
}
