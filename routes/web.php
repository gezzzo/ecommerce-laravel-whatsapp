<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\CheckoutModeMiddleware;
use Illuminate\Support\Facades\Route;

// ==============================
// الصفحة الرئيسية
// ==============================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ==============================
// المصادقة (بالهاتف)
// ==============================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==============================
// البحث والمنتجات
// ==============================
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/offers', [ProductController::class, 'offers'])->name('offers');
Route::get('/categories', [ProductController::class, 'categories'])->name('categories');
Route::get('/category/{slug}', [ProductController::class, 'category'])->name('category');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('product');

// ==============================
// السلة
// ==============================
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

// ==============================
// المفضلة
// ==============================
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
Route::delete('/wishlist/{skuCode}', [WishlistController::class, 'remove'])->name('wishlist.remove');

// ==============================
// الكوبون
// ==============================
Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
Route::delete('/coupon', [CouponController::class, 'destroy'])->name('coupon.destroy');

// ==============================
// الدفع — محمي بـ CheckoutModeMiddleware
// ==============================
Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])
    ->name('checkout.confirmation')
    ->middleware('signed');

Route::middleware(CheckoutModeMiddleware::class)->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// ==============================
// حساب المستخدم
// ==============================
Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
});

Route::get('/switch-language', function () {
    $currentLocale = session('locale', 'ar');
    $newLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    session(['locale' => $newLocale]);

    return back();
})->name('switch-language');

// ==============================
// صفحات ثابتة
// ==============================
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/returns', 'pages.returns')->name('returns');
Route::view('/faq', 'pages.faq')->name('faq');
