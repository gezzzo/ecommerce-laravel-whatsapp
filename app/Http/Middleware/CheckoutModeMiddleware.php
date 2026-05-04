<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckoutModeMiddleware
{
    /**
     * Enforce the checkout authentication mode configured by the store owner.
     *
     * - required: guest users are redirected to login.
     * - optional: guest users can proceed but are offered a login form.
     * - guest:    all users proceed directly with shipping info only.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = StoreSetting::checkoutMode();

        if ($mode === StoreSetting::CHECKOUT_REQUIRED && ! auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'يجب تسجيل الدخول للمتابعة إلى الدفع.');
        }

        // Share the mode with all checkout views
        view()->share('checkoutMode', $mode);

        return $next($request);
    }
}
