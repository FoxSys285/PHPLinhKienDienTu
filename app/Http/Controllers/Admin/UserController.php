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
        $stats = [
            'total' => User::query()->count(),
            'customers' => User::query()->where('role', 'customer')->count(),
            'staff' => User::query()->whereIn('role', ['admin', 'sales', 'warehouse', 'accountant'])->count(),
            'administrators' => User::query()->where('role', 'admin')->count(),
        ];

        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users, 'stats' => $stats]);
    }

    public function update(Request $request, User $user, AuditLogService $auditLogs): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['sometimes', 'required', 'in:customer,admin,sales,warehouse,accountant'],
        ]);

        $role = $data['role'] ?? $user->role;

        if ($user->id === $request->user()->id && $role !== 'admin') {
            return back()->withErrors(['role' => 'Không thể tự xóa quyền quản trị của tài khoản đang đăng nhập.']);
        }

        $oldValues = $user->only(['role']);
        $user->update(['role' => $role]);
        $auditLogs->record('user.permissions_updated', $user, $oldValues, $user->only(['role']), $request->user());

        return back()->with('success', 'Vai trò tài khoản đã được cập nhật.');
    }
}
