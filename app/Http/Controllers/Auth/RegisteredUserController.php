<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, CartController $cartController): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'privacy_accepted' => ['accepted'],
        ]);

        unset($data['privacy_accepted']);

        $user = User::query()->create($data + ['role' => 'customer']);

        Auth::login($user);
        $request->session()->regenerate();
        $cartController->mergeForUser($request, $user);

        return redirect()->route('account.index')
            ->with('success', 'Tài khoản đã được tạo. Bạn có thể mua hàng và theo dõi đơn hàng tại đây.')
            ->withCookie(cookie('cart_token', '', -1));
    }
}
