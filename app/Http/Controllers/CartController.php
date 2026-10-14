<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->currentCart($request);
        $cartItems = $cart->items()->with('product.category')->get();

        return view('layouts.pages.user.giohang', [
            'cartItems' => $cartItems,
            'cartQuantity' => $cartItems->sum('quantity'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);
        $cart = $this->currentCart($request);
        $currentQuantity = (int) ($cart->items()->where('product_id', $product->id)->value('quantity') ?? 0);
        $newQuantity = $currentQuantity + (int) $validated['quantity'];

        if ($product->stock < $newQuantity || $newQuantity > 99) {
            return back()->withErrors([
                'quantity' => 'Số lượng yêu cầu vượt quá số lượng sản phẩm hiện có.',
            ])->withInput();
        }

        $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $newQuantity],
        );

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index', ['selected' => [$product->id]]);
        }

        return redirect()->route('cart.index')->with('cart_status', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart = $this->currentCart($request);
        $items = $cart->items()->with('product')->get();

        foreach ($items as $item) {
            $quantity = $validated['quantities'][$item->product_id] ?? null;

            if ($quantity === null) {
                continue;
            }

            if ($quantity > $item->product->stock) {
                return back()->withErrors([
                    'quantities.'.$item->product_id => 'Số lượng vượt quá tồn kho của sản phẩm.',
                ])->withInput();
            }

            $item->update(['quantity' => $quantity]);
        }

        return redirect()->route('cart.index')->with('cart_status', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $this->currentCart($request)
            ->items()
            ->where('product_id', $validated['product_id'])
            ->delete();

        return redirect()->route('cart.index')->with('cart_status', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    private function currentCart(Request $request): Cart
    {
        return Cart::query()->firstOrCreate(
            ['session_id' => $request->session()->getId()],
            ['user_id' => $request->user()?->id],
        );
    }
}
