<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, CartController $cartController): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where(function ($query) use ($data): void {
            $query->where('email', $data['login'])->orWhere('phone', $data['login']);
        })->first();

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Thông tin đăng nhập chưa chính xác.'])->onlyInput('login');
        }

        $request->session()->regenerate();
        $cartController->mergeForUser($request, $user);

        $destination = $user->role === 'admin' ? route('admin.dashboard') : route('account.index');

        return redirect()->intended($destination)->withCookie(cookie('cart_token', '', -1));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->withCookie(cookie('cart_token', '', -1));
    }
}
