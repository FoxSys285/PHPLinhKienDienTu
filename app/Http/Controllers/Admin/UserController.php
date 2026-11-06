<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('company_name', 'like', $term));
            })
            ->when($request->filled('type'), fn ($query) => $query->where('account_type', $request->string('type')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function update(Request $request, User $user, AuditLogService $auditLogs): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['sometimes', 'required', 'in:customer,admin,sales,warehouse,accountant'],
            'business_approved' => ['nullable', 'boolean'],
            'price_group' => ['required', 'in:retail,wholesale,education'],
        ]);

        $role = $data['role'] ?? $user->role;

        if ($user->id === $request->user()->id && $role !== 'admin') {
            return back()->withErrors(['role' => 'Không thể tự xóa quyền quản trị của tài khoản đang đăng nhập.']);
        }

        $oldValues = $user->only(['role', 'business_approved', 'price_group']);
        $user->update([
            'role' => $role,
            'business_approved' => $request->boolean('business_approved'),
            'price_group' => $data['price_group'],
        ]);
        $auditLogs->record('user.permissions_updated', $user, $oldValues, $user->only(['role', 'business_approved', 'price_group']), $request->user());

        return back()->with('success', 'Vai trò và quyền lợi tài khoản đã được cập nhật.');
    }
}
