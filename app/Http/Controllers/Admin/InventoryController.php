<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StockAvailable;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockAlert;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'stock_status' => ['nullable', Rule::in(['out', 'low', 'normal'])],
            'product_status' => ['nullable', Rule::in(['active', 'inactive'])],
            'sort' => ['nullable', Rule::in(['stock_asc', 'stock_desc', 'name', 'updated_desc'])],
            'activity_q' => ['nullable', 'string', 'max:120'],
            'movement_type' => ['nullable', Rule::in(['opening', 'in', 'adjustment', 'reserve', 'out', 'release'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $productQuery = Product::query()
            ->when(isset($filters['q']), function (Builder $query) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $query->where(fn (Builder $builder) => $builder
                    ->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('brand', 'like', $term));
            })
            ->when(isset($filters['category_id']), fn (Builder $query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['product_status']), fn (Builder $query) => $query->where('is_active', $filters['product_status'] === 'active'))
            ->when(($filters['stock_status'] ?? null) === 'out', fn (Builder $query) => $query->whereColumn('stock', '<=', 'reserved_stock'))
            ->when(($filters['stock_status'] ?? null) === 'low', fn (Builder $query) => $query
                ->whereRaw('stock > reserved_stock AND stock <= reserved_stock + minimum_stock'))
            ->when(($filters['stock_status'] ?? null) === 'normal', fn (Builder $query) => $query
                ->whereRaw('stock > reserved_stock + minimum_stock'))
            ->withCount('inventoryMovements')
            ->when(($filters['sort'] ?? 'stock_asc') === 'stock_desc', fn (Builder $query) => $query->orderByDesc('stock'))
            ->when(($filters['sort'] ?? 'stock_asc') === 'name', fn (Builder $query) => $query->orderBy('name'))
            ->when(($filters['sort'] ?? 'stock_asc') === 'updated_desc', fn (Builder $query) => $query->latest('updated_at'))
            ->when(($filters['sort'] ?? 'stock_asc') === 'stock_asc', fn (Builder $query) => $query->orderBy('stock'))
            ->orderBy('id');
        $products = $productQuery->paginate(20, ['*'], 'products_page')
            ->withQueryString();
        $movementQuery = InventoryMovement::query()
            ->with(['product', 'user'])
            ->when(isset($filters['activity_q']), function (Builder $query) use ($filters): void {
                $term = '%'.$filters['activity_q'].'%';
                $query->where(function (Builder $builder) use ($term): void {
                    $builder->where('reference', 'like', $term)
                        ->orWhere('lot_number', 'like', $term)
                        ->orWhere('note', 'like', $term)
                        ->orWhereHas('product', fn (Builder $productQuery) => $productQuery->where(fn (Builder $searchQuery) => $searchQuery
                            ->where('name', 'like', $term)
                            ->orWhere('sku', 'like', $term)))
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $term));
                });
            })
            ->when(isset($filters['movement_type']), fn (Builder $query) => $query->where('type', $filters['movement_type']))
            ->when(isset($filters['date_from']), fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->latest('created_at')
            ->orderByDesc('id');
        $movements = $movementQuery->paginate(20, ['*'], 'movements_page')
            ->withQueryString();

        $stats = [
            'products' => Product::query()->count(),
            'low_stock' => Product::query()->whereRaw('stock > reserved_stock AND stock <= reserved_stock + minimum_stock')->count(),
            'out_of_stock' => Product::query()->whereColumn('stock', '<=', 'reserved_stock')->count(),
            'movements' => InventoryMovement::query()->count(),
        ];

        return view('admin.inventory.index', [
            'products' => $products,
            'movements' => $movements,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'stats' => $stats,
        ]);
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
