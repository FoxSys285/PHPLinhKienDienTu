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
            'account_type' => ['required', 'in:individual,business'],
            'company_name' => ['required_if:account_type,business', 'nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:30'],
            'privacy_accepted' => ['accepted'],
        ]);

        unset($data['privacy_accepted']);

        $user = User::query()->create($data + [
            'role' => 'customer',
            'price_group' => 'retail',
            'business_approved' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $cartController->mergeForUser($request, $user);

        $successMessage = $user->account_type === 'business'
            ? 'Tài khoản đã được tạo. Hồ sơ doanh nghiệp sẽ hiển thị giá B2B sau khi được duyệt.'
            : 'Tài khoản đã được tạo. Bạn có thể mua hàng và theo dõi đơn hàng tại đây.';

        return redirect()->route('account.index')->with('success', $successMessage)->withCookie(cookie('cart_token', '', -1));
    }
}
