<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $code = $this->input('code');
        $title = $this->input('title');

        $this->merge([
            'code' => is_string($code) ? Str::upper(trim($code)) : $code,
            'slug' => $this->input('slug') ?: (is_string($title) ? Str::slug($title) : null),
            'is_active' => $this->boolean('is_active'),
            'is_automatic' => $this->boolean('is_automatic'),
            'category_slugs' => $this->input('category_slugs', []),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $promotion = $this->route('promotion');

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('coupons', 'slug')->ignore($promotion)],
            'code' => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($promotion)],
            'eyebrow' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0.01', $this->input('type') === 'percentage' ? 'max:100' : 'max:1000000000'],
            'minimum_purchase' => ['required', 'numeric', 'min:0', 'max:1000000000'],
            'maximum_discount' => ['nullable', 'numeric', 'min:1', 'max:1000000000'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', Rule::when($this->filled('starts_at'), ['after_or_equal:starts_at'])],
            'category_slugs' => ['array'],
            'category_slugs.*' => ['string', 'distinct', Rule::exists('categories', 'slug')],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
            'image_preset' => ['nullable', Rule::in(array_keys(Coupon::IMAGE_PRESETS))],
            'theme' => ['required', Rule::in(['blue', 'cyan', 'violet', 'amber'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['boolean'],
            'is_automatic' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'tên chương trình', 'code' => 'mã ưu đãi', 'discount_value' => 'mức giảm',
            'minimum_purchase' => 'giá trị mua tối thiểu', 'maximum_discount' => 'mức giảm tối đa',
            'expires_at' => 'ngày kết thúc', 'starts_at' => 'ngày bắt đầu', 'image' => 'ảnh minh họa',
        ];
    }
}
