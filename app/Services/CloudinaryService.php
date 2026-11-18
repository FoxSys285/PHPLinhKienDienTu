<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    public function uploadProductImage(UploadedFile $image): string
    {
        return $this->uploadImage($image);
    }

    public function uploadPromotionImage(UploadedFile $image): string
    {
        return $this->uploadAsset($image, 'image', (string) config('services.cloudinary.promotions_folder'));
    }

    public function uploadReviewImage(UploadedFile $image): string
    {
        return $this->uploadAsset($image, 'image', (string) config('services.cloudinary.review_images_folder'));
    }

    public function uploadReviewVideo(UploadedFile $video): string
    {
        return $this->uploadAsset($video, 'video', (string) config('services.cloudinary.review_videos_folder'));
    }

    public function importProductImage(string $imageUrl): string
    {
        if (! filter_var($imageUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($imageUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new RuntimeException('URL ảnh sản phẩm không hợp lệ.');
        }

        return $this->uploadImage($imageUrl, [
            'public_id' => 'import-'.hash('sha256', $imageUrl),
            'overwrite' => 'false',
        ]);
    }

    /** @param array{public_id?: string, overwrite?: string} $options */
    private function uploadImage(UploadedFile|string $image, array $options = []): string
    {
        return $this->uploadAsset(
            $image,
            'image',
            (string) config('services.cloudinary.folder'),
            $options,
        );
    }

    /** @param array{public_id?: string, overwrite?: string} $options */
    private function uploadAsset(UploadedFile|string $file, string $resourceType, string $folder, array $options = []): string
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Cloudinary chưa được cấu hình đầy đủ. Vui lòng kiểm tra cấu hình máy chủ.');
        }

        $parameters = [...$options, 'timestamp' => (string) now()->timestamp];
        $folder = trim($folder, '/');

        if ($folder !== '') {
            $parameters['folder'] = $folder;
        }

        ksort($parameters);
        $signatureData = implode('&', array_map(
            static fn (string $key, string $value): string => $key.'='.$value,
            array_keys($parameters),
            array_values($parameters),
        ));

        try {
            $request = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout($resourceType === 'video' ? 120 : 30);

            if ($file instanceof UploadedFile) {
                $request->attach('file', $file->getContent(), $file->hashName(), ['Content-Type' => $file->getMimeType()]);
            } else {
                $request->asForm();
            }

            $response = $request->post('https://api.cloudinary.com/v1_1/'.rawurlencode($cloudName).'/'.$resourceType.'/upload', [
                ...$parameters,
                'api_key' => $apiKey,
                'signature' => hash('sha256', $signatureData.$apiSecret),
                ...(is_string($file) ? ['file' => $file] : []),
            ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Không thể kết nối Cloudinary. Vui lòng thử lại sau.');
        }

        $assetUrl = $response->json('secure_url');

        if (! $response->successful() || ! is_string($assetUrl) || ! filter_var($assetUrl, FILTER_VALIDATE_URL) || parse_url($assetUrl, PHP_URL_SCHEME) !== 'https') {
            $mediaLabel = $resourceType === 'video' ? 'video' : 'ảnh';

            throw new RuntimeException('Không tải được '.$mediaLabel.' lên Cloudinary. Vui lòng thử lại.');
        }

        return $assetUrl;
    }
}
