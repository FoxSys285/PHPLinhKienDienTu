<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StockAvailable;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockAlert;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('stock', '<=', 'minimum_stock'))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->withCount('inventoryMovements')
            ->orderBy('stock')
            ->paginate(20)
            ->withQueryString();
        $movements = InventoryMovement::query()->with(['product', 'user'])->latest()->take(12)->get();

        return view('admin.inventory.index', ['products' => $products, 'movements' => $movements]);
    }

    public function store(Request $request, Product $product, AuditLogService $auditLogs): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,adjustment'],
            'quantity' => ['required', 'integer', 'between:-1000000,1000000', 'not_in:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'lot_number' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $quantity = (int) $data['quantity'];

        if ($data['type'] === 'in' && $quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Phiếu nhập kho cần số lượng dương.']);
        }

        $notifySubscribers = false;

        DB::transaction(function () use ($request, $product, $data, $quantity, $auditLogs, &$notifySubscribers): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $oldStock = $lockedProduct->stock;
            $stockChange = $data['type'] === 'in' ? abs($quantity) : $quantity;
            $newStock = $lockedProduct->stock + $stockChange;

            if ($newStock < $lockedProduct->reserved_stock) {
                throw ValidationException::withMessages(['quantity' => 'Tồn kho không thể thấp hơn số lượng đang giữ cho đơn hàng.']);
            }

            $lockedProduct->update(['stock' => $newStock]);
            $notifySubscribers = $newStock > $oldStock && $lockedProduct->availableStock() > 0;
            $note = trim(implode(' · ', array_filter([$data['supplier'] ?? null, $data['note'] ?? null])));
            $lockedProduct->inventoryMovements()->create([
                'user_id' => $request->user()->id,
                'type' => $data['type'],
                'quantity' => $stockChange,
                'reference' => 'INV-'.now()->format('ymd-His'),
                'lot_number' => $data['lot_number'] ?? null,
                'note' => $note !== '' ? $note : null,
            ]);
            $auditLogs->record('inventory.updated', $lockedProduct, ['stock' => $oldStock], ['stock' => $newStock, 'movement' => $stockChange], $request->user());
        });

        if ($notifySubscribers) {
            $this->notifyRestockSubscribers($product);
        }

        return back()->with('success', 'Tồn kho đã được cập nhật và ghi nhật ký.');
    }

    private function notifyRestockSubscribers(Product $product): void
    {
        $alerts = StockAlert::query()->where('product_id', $product->id)->whereNull('notified_at')->get();

        foreach ($alerts as $alert) {
            try {
                Mail::to($alert->email)->send(new StockAvailable($product));
                $alert->update(['notified_at' => now()]);
            } catch (Throwable $exception) {
                Log::warning('Stock availability email could not be sent.', [
                    'product_id' => $product->id,
                    'stock_alert_id' => $alert->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }
}
