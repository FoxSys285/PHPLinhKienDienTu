<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Services\AuditLogService;
use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PromotionController extends Controller
{
    public function __construct(private CloudinaryService $cloudinary) {}

    public function index(Request $request): View
    {
        $query = Coupon::query()->promoted();
        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->available()->count(),
            'scheduled' => (clone $query)->where('is_active', true)->where('starts_at', '>', now())->count(),
            'uses' => (int) (clone $query)->sum('used_count'),
        ];

        $query->when($request->filled('q'), function (Builder $query) use ($request): void {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(fn (Builder $query): Builder => $query->where('title', 'like', $term)->orWhere('code', 'like', $term));
        });

        match ($request->string('status')->toString()) {
            'active' => $query->available(),
            'paused' => $query->where('is_active', false),
            'scheduled' => $query->where('is_active', true)->where('starts_at', '>', now()),
            'expired' => $query->where('expires_at', '<', now()),
            'exhausted' => $query->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit'),
            default => null,
        };

        return view('admin.promotions.index', [
            'promotions' => $query->orderBy('sort_order')->orderByDesc('id')->paginate(15)->withQueryString(),
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return $this->form(new Coupon(['type' => 'percentage', 'discount_value' => 10, 'minimum_purchase' => 0, 'theme' => 'blue', 'sort_order' => 0, 'is_active' => true, 'is_automatic' => true]));
    }

    public function store(PromotionRequest $request, AuditLogService $auditLogs): RedirectResponse
    {
        $promotion = Coupon::query()->create($this->data($request));
        $auditLogs->record('promotion.created', $promotion, null, $promotion->toArray(), $request->user());

        return redirect()->route('admin.promotions.index')->with('success', 'Đã thêm chương trình ưu đãi.');
    }

    public function edit(Coupon $promotion): View
    {
        return $this->form($promotion);
    }

    public function update(PromotionRequest $request, Coupon $promotion, AuditLogService $auditLogs): RedirectResponse
    {
        $before = $promotion->toArray();
        $promotion->update($this->data($request, $promotion));
        $auditLogs->record('promotion.updated', $promotion, $before, $promotion->toArray(), $request->user());

        return redirect()->route('admin.promotions.index')->with('success', 'Đã cập nhật chương trình ưu đãi.');
    }

    public function destroy(Request $request, Coupon $promotion, AuditLogService $auditLogs): RedirectResponse
    {
        $promotion->update(['is_active' => false]);
        $auditLogs->record('promotion.paused', $promotion, null, ['is_active' => false], $request->user());

        return redirect()->route('admin.promotions.index')->with('success', 'Đã tạm dừng ưu đãi. Lịch sử sử dụng được giữ lại.');
    }

    private function form(Coupon $promotion): View
    {
        return view('admin.promotions.form', [
            'promotion' => $promotion,
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'imagePresets' => Coupon::IMAGE_PRESETS,
        ]);
    }

    /** @return array<string, mixed> */
    private function data(PromotionRequest $request, ?Coupon $promotion = null): array
    {
        $data = $request->safe()->except(['image', 'image_preset']);

        if ($request->hasFile('image')) {
            try {
                $data['image_path'] = $this->cloudinary->uploadPromotionImage($request->file('image'));
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages(['image' => $exception->getMessage()]);
            }
        } elseif ($request->filled('image_preset')) {
            $preset = $request->string('image_preset')->toString();
            $data['image_path'] = match ($preset) {
                'tools' => 'images/home-promotion-tools.webp',
                'chips' => 'images/home-hero-cpu.webp',
                default => 'images/promotions/'.$preset.'.png',
            };
        } elseif (! $promotion?->image_path) {
            $data['image_path'] = 'images/promotions/maker.png';
        }

        return $data;
    }
}
