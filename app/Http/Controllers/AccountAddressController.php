<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountAddressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'new_address' => ['required', 'array:recipient_name,recipient_phone,province_code,province_name,ward_code,ward_name,address_line,note,is_default'],
            'new_address.recipient_name' => ['required', 'string', 'max:150'],
            'new_address.recipient_phone' => ['required', 'string', 'max:30'],
            'new_address.province_code' => ['required', 'string', 'max:10'],
            'new_address.province_name' => ['required', 'string', 'max:120'],
            'new_address.ward_code' => ['required', 'string', 'max:10'],
            'new_address.ward_name' => ['required', 'string', 'max:120'],
            'new_address.address_line' => ['required', 'string', 'max:500'],
            'new_address.note' => ['nullable', 'string', 'max:500'],
            'new_address.is_default' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $addressData = $validated['new_address'];
        $addressData['label'] = $addressData['recipient_name'];
        $isDefault = $request->boolean('new_address.is_default') || ! $user->addresses()->exists();

        DB::transaction(function () use ($user, $addressData, $isDefault): void {
            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create(array_merge($addressData, ['is_default' => $isDefault]));
        });

        return back()->with('success', 'Địa chỉ giao hàng đã được thêm.');
    }

    public function setDefault(Request $request, UserAddress $address): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($address->getKey());

        DB::transaction(function () use ($user, $address): void {
            $user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('success', 'Địa chỉ mặc định đã được cập nhật.');
    }

    public function destroy(Request $request, UserAddress $address): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($address->getKey());
        $wasDefault = $address->is_default;

        DB::transaction(function () use ($user, $address, $wasDefault): void {
            $address->delete();

            if ($wasDefault) {
                $user->addresses()->latest()->first()?->update(['is_default' => true]);
            }
        });

        return back()->with('success', 'Địa chỉ giao hàng đã được xóa.');
    }
}
