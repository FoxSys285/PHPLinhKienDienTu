<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\CloudinaryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

#[Signature('products:cloudinary-images {--dry-run : Chỉ kiểm tra, không tải ảnh hoặc cập nhật sản phẩm}')]
#[Description('Chuyển ảnh sản phẩm hiện tại lên Cloudinary và sao lưu URL gốc.')]
class ImportProductImagesToCloudinary extends Command
{
    public function handle(CloudinaryService $cloudinary): int
    {
        $cloudinaryPrefix = 'https://res.cloudinary.com/'.config('services.cloudinary.cloud_name').'/';
        $products = Product::query()
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->orderBy('id')
            ->get(['id', 'sku', 'image_url'])
            ->reject(fn (Product $product): bool => str_starts_with($product->image_url, $cloudinaryPrefix));

        if ($products->isEmpty()) {
            $this->info('Không còn ảnh sản phẩm cần chuyển.');

            return self::SUCCESS;
        }

        $groups = $products->groupBy('image_url');
        $this->info("Cần chuyển {$products->count()} sản phẩm, {$groups->count()} URL ảnh.");

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $backupPath = 'cloudinary-imports/'.now()->format('Ymd-His').'-'.Str::uuid().'.json';

        if (! Storage::disk('local')->put($backupPath, $products->values()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))) {
            $this->error('Không tạo được bản sao URL gốc. Chưa thay đổi sản phẩm.');

            return self::FAILURE;
        }

        $this->info('Bản sao URL gốc: '.$backupPath);
        $updated = 0;
        $failed = 0;

        foreach ($groups as $sourceUrl => $group) {
            $skus = $group->pluck('sku')->implode(', ');

            try {
                $imageUrl = $cloudinary->importProductImage($sourceUrl);
            } catch (RuntimeException $exception) {
                $failed += $group->count();
                $this->warn($skus.': '.$exception->getMessage());

                continue;
            }

            $count = $group->toQuery()->where('image_url', $sourceUrl)->update(['image_url' => $imageUrl]);
            $updated += $count;
            $this->line($skus.": đã cập nhật {$count} sản phẩm.");
        }

        $this->info("Đã cập nhật {$updated} sản phẩm; lỗi {$failed} sản phẩm.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
