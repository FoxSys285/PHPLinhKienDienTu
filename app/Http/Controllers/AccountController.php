<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest as ProductReturnRequest;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.index', [
            'orders' => $request->user()->orders()->latest()->take(8)->get(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user(),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function orderHistory(Request $request): View
    {
        return view('account.orders', [
            'orders' => $request->user()->orders()->withCount('items')->latest()->paginate(10)->withQueryString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone,'.$request->user()->id],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:30'],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Hồ sơ đã được cập nhật.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Mật khẩu đã được thay đổi.');
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return view('account.order', ['order' => $order->load('items', 'returnRequests', 'shipmentEvents', 'paymentIntent')]);
    }

    public function cancelOrder(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $orders->cancel($order, $data['reason'], $request->user());

        return back()->with('success', 'Đơn hàng đã được hủy và lý do đã được lưu.');
    }

    public function requestReturn(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if ($order->status !== 'completed' || $order->created_at?->lt(now()->subDays(7))) {
            return back()->withErrors(['return' => 'Đơn hàng không còn trong thời hạn tiếp nhận yêu cầu đổi trả 7 ngày.']);
        }

        if (
            $order->returnRequests()->whereIn('status', ['pending', 'accepted'])->exists()
            || in_array($order->refund_status, ['pending', 'refunded'], true)
        ) {
            return back()->withErrors(['return' => 'Đơn hàng đã có yêu cầu đổi trả đang xử lý hoặc đã hoàn tiền.']);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:20480'],
        ]);

        $evidencePath = isset($data['evidence'])
            ? $data['evidence']->store('return-evidence', 'local')
            : null;

        ProductReturnRequest::query()->create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'evidence_path' => $evidencePath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Yêu cầu đổi trả đã được gửi. Bộ phận kinh doanh sẽ liên hệ với bạn.');
    }
}
