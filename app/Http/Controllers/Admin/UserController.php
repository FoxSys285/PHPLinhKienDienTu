<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,blocked'],
        ]);

        $users = User::query()
            ->where('role', '!=', 'Admin')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('is_blocked', $filters['status'] === 'blocked'))
            ->withCount('orders')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('layouts.pages.admin.quanlynguoidung', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function show(User $user): View
    {
        abort_if($user->role === 'Admin', 404);

        $user->loadCount('orders')->load(['orders' => fn ($query) => $query->latest()->limit(10)]);

        return view('layouts.pages.admin.chitietnguoidung', [
            'user' => $user,
        ]);
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'Admin', 404);

        $validated = $request->validate([
            'is_blocked' => ['required', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $isBlocked = (bool) $validated['is_blocked'];
        $user->update([
            'is_blocked' => $isBlocked,
            'blocked_reason' => $isBlocked ? ($validated['blocked_reason'] ?? null) : null,
        ]);

        return back()->with('status', $isBlocked ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
    }
}
