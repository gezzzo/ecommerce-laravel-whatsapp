<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request): RedirectResponse
    {
        return back()->with('error', 'كود الخصم غير صالح.');
    }
}
