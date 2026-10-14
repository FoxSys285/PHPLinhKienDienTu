<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $vouchers = Voucher::query()
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where('code', 'like', '%'.Str::upper($term).'%'))
            ->when(isset($filters['status']), fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('layouts.pages.admin.quanlykhuyenmai', [
            'vouchers' => $vouchers,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('layouts.pages.admin.formkhuyenmai', [
            'voucher' => new Voucher(['is_active' => true, 'minimum_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Voucher::query()->create($this->validatedData($request));

        return redirect()->route('admin.vouchers.index')->with('status', 'Đã tạo mã khuyến mãi.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('layouts.pages.admin.formkhuyenmai', [
            'voucher' => $voucher,
        ]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($this->validatedData($request, $voucher));

        return redirect()->route('admin.vouchers.index')->with('status', 'Đã cập nhật mã khuyến mãi.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        if ($voucher->orders()->exists()) {
            return back()->withErrors(['voucher' => 'Mã này đã gắn với đơn hàng. Hãy tắt trạng thái sử dụng thay vì xóa.']);
        }

        $voucher->delete();

        return back()->with('status', 'Đã xóa mã khuyến mãi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge([
            'code' => Str::upper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('vouchers', 'code')->ignore($voucher),
            ],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'max_discount' => ['nullable', 'numeric', 'gt:0', 'max:9999999999'],
            'minimum_order' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'Mức giảm theo phần trăm không được vượt quá 100%.',
            ]);
        }

        if ($voucher !== null && isset($validated['usage_limit']) && $validated['usage_limit'] < $voucher->used_count) {
            throw ValidationException::withMessages([
                'usage_limit' => 'Giới hạn lượt dùng không thể thấp hơn số lượt đã sử dụng.',
            ]);
        }

        return [
            ...$validated,
            'max_discount' => $validated['discount_type'] === 'percent' ? ($validated['max_discount'] ?? null) : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
