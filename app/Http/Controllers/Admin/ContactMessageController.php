<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total' => ContactMessage::query()->count(),
            'new' => ContactMessage::query()->where('status', 'new')->count(),
            'in_progress' => ContactMessage::query()->where('status', 'in_progress')->count(),
            'resolved' => ContactMessage::query()->where('status', 'resolved')->count(),
        ];

        $messages = ContactMessage::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('subject', 'like', $term)->orWhere('message', 'like', $term));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'in_progress', 'resolved'])],
        ]);

        $contactMessage->update($data);

        return redirect()->route('admin.contact-messages.index')->with('success', 'Đã cập nhật trạng thái yêu cầu liên hệ.');
    }
}
