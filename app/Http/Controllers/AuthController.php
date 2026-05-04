<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     * Hidden when checkout_mode is guest-only.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->intended(route('home'));
        }

        $mode = StoreSetting::checkoutMode();

        return view('auth.login', compact('mode'));
    }

    /**
     * Handle login via phone + password.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => User::normalizePhone((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
        ], [
            'phone.required'    => 'رقم الهاتف مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        if (Auth::attempt(['phone' => $validated['phone'], 'password' => $validated['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))->with('success', 'مرحباً بعودتك! 👋');
        }

        return back()->withErrors(['phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.'])->withInput();
    }

    /**
     * Show registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        $mode = StoreSetting::checkoutMode();

        return view('auth.register', compact('mode'));
    }

    /**
     * Handle registration via phone + password.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => User::normalizePhone((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'phone'                 => ['required', 'string', 'max:20'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'         => 'الاسم مطلوب.',
            'phone.required'        => 'رقم الهاتف مطلوب.',
            'password.required'     => 'كلمة المرور مطلوبة.',
            'password.min'          => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.confirmed'    => 'كلمتا المرور غير متطابقتين.',
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if ($user && ! $user->is_guest) {
            return back()
                ->withErrors(['phone' => 'رقم الهاتف مسجل بالفعل.'])
                ->withInput();
        }

        if ($user) {
            $user->update([
                'name'     => $validated['name'],
                'password' => Hash::make($validated['password']),
                'is_guest' => false,
            ]);
        } else {
            $user = User::create([
                'name'     => $validated['name'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'is_guest' => false,
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('home'))->with('success', 'تم إنشاء حسابك بنجاح! مرحباً بك 🎉');
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
