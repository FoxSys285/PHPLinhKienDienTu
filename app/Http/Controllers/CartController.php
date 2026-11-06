<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class CartController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->currentCart($request);
        $cart->load('items.product.priceTiers');
        $priceGroup = $this->priceGroup($request->user());
        $subtotal = $cart->items->sum(fn (CartItem $item): float => $item->product
            ? $item->product->priceFor((int) $item->quantity, $priceGroup) * $item->quantity
            : 0);

        return response()->view('cart.index', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'shippingFee' => $subtotal >= (float) config('store.free_shipping_threshold') ? 0 : (float) config('store.standard_shipping_fee'),
        ])->withCookie($this->cartCookie($cart));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:1000000']]);
        abort_unless($product->is_active, 404);

        $cart = $this->currentCart($request);
        $existing = $cart->items()->where('product_id', $product->id)->first();
        $quantity = (int) $validated['quantity'] + (int) ($existing?->quantity ?? 0);

        if ($quantity < $product->moq || $quantity % max(1, $product->pack_size) !== 0) {
            return back()->withErrors(['quantity' => "Số lượng phải từ {$product->moq} và theo bội số {$product->pack_size}."])->withCookie($this->cartCookie($cart));
        }

        if (! $product->allow_backorder && $quantity > $product->availableStock()) {
            return back()->withErrors(['quantity' => 'Số lượng vượt quá tồn kho khả dụng.'])->withCookie($this->cartCookie($cart));
        }

        $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $quantity],
        );

        return redirect()->route('cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng.')->withCookie($this->cartCookie($cart));
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->currentCart($request);
        $item = $cart->items()->whereKey($item->id)->with('product')->firstOrFail();
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:1000000']]);
        $product = $item->product;
        $quantity = (int) $validated['quantity'];

        if ($quantity < $product->moq || $quantity % max(1, $product->pack_size) !== 0) {
            return back()->withErrors(['quantity' => "{$product->name}: số lượng phải từ {$product->moq} và theo bội số {$product->pack_size}."]);
        }

        if (! $product->allow_backorder && $quantity > $product->availableStock()) {
            return back()->withErrors(['quantity' => "{$product->name} hiện không đủ tồn kho."]);
        }

        $item->update(['quantity' => $quantity]);

        return back()->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->currentCart($request);
        $cart->items()->whereKey($item->id)->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function mergeForUser(Request $request, User $user): Cart
    {
        $userCart = Cart::query()->firstOrCreate(['user_id' => $user->id, 'token' => null]);
        $guestToken = $request->cookie('cart_token');

        if (is_string($guestToken) && $guestToken !== '') {
            $guestCart = Cart::query()->where('token', $guestToken)->first();

            if ($guestCart) {
                foreach ($guestCart->items as $guestItem) {
                    $userItem = $userCart->items()->where('product_id', $guestItem->product_id)->first();
                    $userCart->items()->updateOrCreate(
                        ['product_id' => $guestItem->product_id],
                        ['quantity' => (int) $guestItem->quantity + (int) ($userItem?->quantity ?? 0)],
                    );
                }

                $guestCart->delete();
            }
        }

        return $userCart;
    }

    public function currentCart(Request $request): Cart
    {
        if ($request->user()) {
            $userCart = Cart::query()->where('user_id', $request->user()->id)->latest('id')->first();

            return $userCart ?? Cart::query()->create(['user_id' => $request->user()->id]);
        }

        $token = $request->cookie('cart_token');
        $cart = is_string($token) ? Cart::query()->where('token', $token)->where(function ($query): void {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->first() : null;

        if ($cart) {
            return $cart;
        }

        return Cart::query()->create([
            'token' => Str::random(60),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function cartCookie(Cart $cart): Cookie
    {
        return cookie('cart_token', (string) $cart->token, 60 * 24 * 7, '/', null, request()->isSecure(), true, false, 'lax');
    }

    private function priceGroup(?User $user): string
    {
        return $user?->account_type === 'business' && $user->business_approved ? $user->price_group : 'retail';
    }
}
