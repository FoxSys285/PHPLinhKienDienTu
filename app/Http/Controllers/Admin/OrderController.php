<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest as ProductReturnRequest;
use App\Services\MockViettelPostService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('order_number', 'like', $term)->orWhere('customer_name', 'like', $term)->orWhere('customer_phone', 'like', $term));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items', 'user', 'shipmentEvents', 'canceller', 'paymentIntent', 'returnRequests')]);
    }

    public function confirm(Order $order, Request $request, OrderService $orders): RedirectResponse
    {
        $orders->confirm($order, $request->user());

        return back()->with('success', 'Đơn hàng đã được xác nhận và cập nhật tồn kho.');
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:handed_over,in_transit,out_for_delivery,delivery_failed,returning,returned,completed'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $source = config('services.viettelpost.driver') === 'mock' ? 'simulation' : 'admin';
        $description = $data['description'] ?? null;

        if ($source === 'simulation' && ! $description) {
            $description = 'Mô phỏng quét vận đơn Viettel Post.';
        }

        $orders->transitionShipment($order, $data['status'], $source, null, $description, now(), null, $request->user());

        return back()->with('success', 'Hành trình vận chuyển đã được cập nhật và ghi nhật ký.');
    }

    public function cancel(Order $order, Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $orders->cancel($order, $data['reason'], $request->user());

        return back()->with('success', 'Đơn hàng đã được hủy, lưu lý do và cập nhật tồn kho.');
    }

    public function createShipment(Request $request, Order $order, OrderService $orders, MockViettelPostService $mockViettelPost): RedirectResponse
    {
        if (config('services.viettelpost.driver') === 'mock') {
            $orders->recordShipment($order, $mockViettelPost->createShipment($order), $request->user(), 'simulation');

            return back()->with('success', 'Đã tạo vận đơn Viettel Post mô phỏng và đặt ngày giao dự kiến sau 3 ngày.');
        }

        $data = $request->validate([
            'shipment_number' => ['required', 'string', 'max:100'],
            'shipment_service' => ['nullable', 'string', 'max:100'],
            'shipment_tracking_url' => ['nullable', 'url', 'max:500'],
            'estimated_delivery_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $orders->recordShipment($order, $data, $request->user());

        return back()->with('success', 'Đã lưu mã vận đơn và chuyển đơn sang chờ Viettel Post nhận kiện.');
    }

    public function updateCodStatus(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $data = $request->validate(['cod_status' => ['required', 'in:collected,pending_reconciliation,reconciled']]);
        $orders->updateCodStatus($order, $data['cod_status'], $request->user());

        return back()->with('success', 'Trạng thái thu hộ COD đã được cập nhật.');
    }

    public function markRefunded(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $data = $request->validate(['refund_reference' => ['nullable', 'string', 'max:120']]);
        $reference = $orders->markRefunded($order, $data['refund_reference'] ?? null, $request->user());

        $message = config('services.payment.driver') === 'mock'
            ? 'Đã mô phỏng hoàn tiền. Mã giao dịch: '.$reference.'. Không có tiền thật được chuyển.'
            : 'Đã ghi nhận hoàn tiền với mã giao dịch '.$reference.'.';

        return back()->with('success', $message);
    }

    public function receiveReturn(ProductReturnRequest $returnRequest, Request $request, OrderService $orders): RedirectResponse
    {
        $orders->receiveReturnRequest($returnRequest, $request->user());

        return back()->with('success', 'Đã xác nhận nhận hàng hoàn. Khoản hoàn tiền đang chờ kế toán xử lý.');
    }

    public function downloadReturnEvidence(ProductReturnRequest $returnRequest): StreamedResponse
    {
        abort_unless($returnRequest->evidence_path && Storage::disk('local')->exists($returnRequest->evidence_path), 404);

        return Storage::disk('local')->download($returnRequest->evidence_path);
    }

    public function updateReturn(Request $request, ProductReturnRequest $returnRequest, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
            'resolution' => ['required_if:status,accepted', 'nullable', 'in:exchange,refund,warranty'],
            'staff_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $resolution = $data['status'] === 'accepted' ? ($data['resolution'] ?? null) : null;
        $orders->reviewReturnRequest($returnRequest, $data['status'], $resolution, $data['staff_note'] ?? null, $request->user());

        $message = $resolution === 'refund'
            ? 'Yêu cầu hoàn tiền đã được duyệt. Chờ cửa hàng nhận hàng trả về trước khi kế toán xử lý hoàn tiền.'
            : 'Yêu cầu đổi trả đã được cập nhật.';

        return back()->with('success', $message);
    }
}
