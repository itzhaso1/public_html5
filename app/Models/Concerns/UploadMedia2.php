<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

trait UploadMedia2 {
    private function publicUploadsUrl(string $disk, string $uploadsPath): string
    {
        $uploadsPath = ltrim($uploadsPath, '/');
        $disk = strtolower(trim($disk));

        $directFilePath = public_path($uploadsPath);
        $storageFilePath = storage_path("app/public/{$uploadsPath}");
        $directUrl = asset($uploadsPath);
        $storageUrl = asset("storage/{$uploadsPath}");

        if ($disk === 'storage_public') {
            // Prefer storage path, then fallback to direct public if legacy/migrated files are mixed.
            if (is_file($storageFilePath)) {
                return $storageUrl;
            }
            if (is_file($directFilePath)) {
                return $directUrl;
            }
            return $storageUrl;
        }

        if ($disk === 'direct_public') {
            // Prefer direct public path, then fallback to storage path if needed.
            if (is_file($directFilePath)) {
                return $directUrl;
            }
            if (is_file($storageFilePath)) {
                return $storageUrl;
            }
            return $directUrl;
        }

        // Unknown/empty disk from legacy rows: auto-detect existing file location.
        if (is_file($directFilePath)) {
            return $directUrl;
        }
        if (is_file($storageFilePath)) {
            return $storageUrl;
        }

        // Final fallback keeps old behavior stable even if file is missing.
        return $directUrl;
    }
    public function uploadSingleMedia(
        $baseFolder,
        UploadedFile $file,
        $model,
        ?string $column = null,
        ?string $relation = null,
        bool $useStorage = false,
        bool $generateThumbnail = false,
        ?string $collectionName = null,
        bool $addWatermark = false,
        int $topCropPx = 0,
        bool $blurTopRightName = false
    ) {
        $disk = $useStorage ? 'local' : 'public';
        $folderPath = "/uploads/$baseFolder";
        if (!$this->isValidImage($file)) {
            throw new \Exception("الصورة غير صحيحة أو تالفة.");
        }
        if ($useStorage) {
            $publicPath = public_path($folderPath);
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0777, true);
            }
        } else {
            $storagePath = storage_path("app/public/$folderPath");
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0777, true);
            }
        }
        $extension = $file->getClientOriginalExtension();
        $fileName = uniqid() . '.' . $extension;
        $filePath = "$folderPath/$fileName";
        $sourcePath = $file->getPathname();
        if ($topCropPx <= 0) {
            $topCropPx = (int) config('account_image.top_crop_px', 0);
        }
        $image = Image::make($sourcePath);
        $this->applyTopCrop($image, $topCropPx);
        $this->applyTopRightNameBlur($image, $blurTopRightName);
        if ($addWatermark) {
            $watermark = Image::make(storage_path('app/public/watermark.png'));
            $image->insert($watermark, 'bottom-right', 10, 10);
        }
        if ($useStorage) {
            $image->save(public_path($filePath));
        } else {
            $image->save(storage_path("app/public/$filePath"));
        }
        if ($generateThumbnail) {
            // Build thumbnail from a fresh image instance to avoid double-crop side effects.
            $this->generateThumbnail($sourcePath, $folderPath, $fileName, $useStorage, $topCropPx);
        }
        $collectionName = $collectionName ?? array_search($file, request()->allFiles(), true) ?? 'default';
        if ($relation) {
            $media = $model->$relation()->create([
                'file_name' => $fileName,
                'disk' => $useStorage ? 'direct_public' : 'storage_public',
                'mediable_id'   => $model->id,
                'mediable_type' => get_class($model),
                'collection_name' => $collectionName,
            ]);
            if (!$media) {
                throw new \Exception("فشل حفظ الميديا في قاعدة البيانات.");
            }
        } elseif ($column) {
            $model->update([$column => $fileName]);
        }
        return $fileName;
    }

    public function updateSingleMedia(
        $baseFolder,
        UploadedFile $file,
        $model,
        ?string $column = null,
        ?string $relation = null,
        bool $useStorage = false,
        bool $generateThumbnail = false,
        ?string $collectionName = null,
        bool $addWatermark = false,
        int $topCropPx = 0,
        bool $blurTopRightName = false
    ) {
        $this->deleteExistingMedia($baseFolder, $model, $column, $relation, $useStorage, $collectionName);
        return $this->uploadSingleMedia(
            $baseFolder,
            $file,
            $model,
            $column,
            $relation,
            $useStorage,
            $generateThumbnail,
            $collectionName,
            $addWatermark,
            $topCropPx,
            $blurTopRightName
        );
    }

    public function deleteExistingMedia($baseFolder, $model, ?string $column, ?string $relation, bool $useStorage, ?string $collectionName)
    {
        $base = "uploads/$baseFolder";

        if ($column && in_array($column, $model->getFillable())) {
            $fileName = $model->{$column};
            if ($fileName) {
                $this->deleteFile($base, $fileName, $useStorage);
            }
        } elseif ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();
            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }
            $media = $query->first();
            if ($media) {
                $this->deleteFile($base, $media->file_name, $useStorage);
                $media->delete();
            }
        }
    }


    public function deleteFile($base, $fileName, bool $useStorage) {
        $originalPath = $useStorage ? public_path("$base/$fileName") : storage_path("app/public/$base/$fileName");
        $thumbnailPath = $useStorage ? public_path("$base/thumbnails/$fileName") : storage_path("app/public/$base/thumbnails/$fileName");

        if (file_exists($originalPath))
            unlink($originalPath);


        if (file_exists($thumbnailPath))
            unlink($thumbnailPath);
    }

    private function isValidImage(UploadedFile $file) {
        try {
            $image = Image::make($file->getRealPath());
            return in_array($image->mime(), ['image/jpeg', 'image/png', 'image/webp']);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generateThumbnail(string $sourcePath, string $folderPath, string $fileName, bool $useStorage, int $topCropPx = 0)
    {
        $thumbnailFolderPath = "$folderPath/thumbnails";
        $thumbnailPath = "$thumbnailFolderPath/$fileName";
        if ($useStorage) {
            $publicThumbnailPath = public_path($thumbnailFolderPath);
            if (!file_exists($publicThumbnailPath)) {
                mkdir($publicThumbnailPath, 0777, true);
            }
        } else {
            $storageThumbnailPath = storage_path("app/public/$thumbnailFolderPath");
            if (!file_exists($storageThumbnailPath)) {
                mkdir($storageThumbnailPath, 0777, true);
            }
        }
        // Always process thumbnail on a separate instance from original image.
        $thumbnail = Image::make($sourcePath);
        $this->applyTopCrop($thumbnail, $topCropPx);
        // Crop is applied first, then resize as requested.
        $thumbnail = $thumbnail->resize(200, 200)->encode();
        if ($useStorage) {
            $thumbnail->save(public_path($thumbnailPath));
        } else {
            $thumbnail->save(storage_path("app/public/$thumbnailPath"));
        }
    }

    public function getMediaUrls($baseFolder, $model, ?string $column = null, ?string $relation = null, ?string $collectionName = null)
    {
        if (!$model) {
            return [];
        }
        $base = "$baseFolder/uploads/" . class_basename($model);
        $images = [];
        if ($column && in_array($column, $model->getFillable())) {
            $fileName = $model->{$column};
            if ($fileName) {
                $images['original'] = $this->publicUploadsUrl('direct_public', "{$base}/{$fileName}");
                $images['thumbnail'] = $this->publicUploadsUrl('direct_public', "{$base}/thumbnails/{$fileName}");
            }
        } elseif ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();
            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }
            $media = $query->first();
            if ($media) {
                $disk = (string) ($media->disk ?? '');
                $fileName = $media->file_name;
                $images['original'] = $this->publicUploadsUrl($disk, "{$base}/{$fileName}");
                $images['thumbnail'] = $this->publicUploadsUrl($disk, "{$base}/thumbnails/{$fileName}");
            }
        }
        return $images;
    }

    public function getMediaUrl(
        string $baseFolder,
        $model,
        ?string $column = null,
        ?string $relation = null,
        ?string $collectionName = null
    ): ?string {
        if (!$model) return null;

        $uploadsBase = "uploads/$baseFolder";
        if ($column && in_array($column, $model->getFillable())) {
            $fileName = $model->{$column};
            if ($fileName) {
                return $this->publicUploadsUrl('direct_public', "{$uploadsBase}/{$fileName}");
            }
        }
        if ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();
            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }
            $media = $query->first();
            if ($media) {
                $fileName = $media->file_name;
                $disk = (string) ($media->disk ?? '');
                return $this->publicUploadsUrl($disk, "{$uploadsBase}/{$fileName}");
            }
        }
        return null;
    }

    /*public function uploadMultipleMedia(
        string $baseFolder,
        array $files,
        $model,
        ?string $relation = null,
        bool $useStorage = false,
        bool $generateThumbnail = false,
        ?string $collectionName = null,
        bool $addWatermark = false
    ): array {
        $uploadedFiles = [];
        $disk = $useStorage ? 'local' : 'public';
        $folderPath = "/uploads/$baseFolder";
        $fullPath = $useStorage
            ? public_path($folderPath)
            : storage_path("app/public/$folderPath");
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            if (!$this->isValidImage($file)) {
                continue;
            }
            $extension = $file->getClientOriginalExtension();
            $fileName = uniqid() . '.' . $extension;
            $filePath = "$folderPath/$fileName";
            $image = Image::make($file->getPathname());

            if ($addWatermark) {
                $watermark = Image::make(storage_path('app/public/watermark.png'));
                $image->insert($watermark, 'bottom-right', 10, 10);
            }

            if ($useStorage) {
                //$image->save(storage_path("app/public/$filePath"));
                $image->save(public_path($filePath));
            } else {
                //$image->save(public_path($filePath));
                $image->save(storage_path("app/public/$filePath"));

            }

            if ($generateThumbnail) {
                $this->generateThumbnail($image, $folderPath, $fileName, $useStorage);
            }

            if ($relation && method_exists($model, $relation)) {
                $model->$relation()->create([
                    'file_name' => $fileName,
                    'disk' => $useStorage ? 'direct_public' : 'storage_public',
                    'mediable_id' => $model->id,
                    'mediable_type' => get_class($model),
                    'collection_name' => $collectionName ?? 'gallery',
                ]);
            }
            $uploadedFiles[] = $fileName;
        }
        return $uploadedFiles;
    }*/
    public function uploadMultipleMedia(
        string $baseFolder,
        array $files,
        $model,
        ?string $relation = null,
        bool $useStorage = false,
        bool $generateThumbnail = false,
        ?string $collectionName = null,
        bool $addWatermark = false,
        int $topCropPx = 0
    ): array {
        $uploadedFiles = [];

        // المسار داخل public مباشرة
        $folderPath = "uploads/$baseFolder";
        $fullPath = public_path($folderPath);

        // إنشاء المجلد إذا مش موجود
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$this->isValidImage($file)) {
                continue;
            }

            $extension = $file->getClientOriginalExtension();
            $fileName = uniqid() . '.' . $extension;
            $filePath = $fullPath . '/' . $fileName;
            $sourcePath = $file->getPathname();
            $image = Image::make($sourcePath);
            $this->applyTopCrop($image, $topCropPx);

            // إضافة العلامة المائية لو مطلوبة
            if ($addWatermark && file_exists(storage_path('app/public/watermark.png'))) {
                $watermark = Image::make(storage_path('app/public/watermark.png'));
                $image->insert($watermark, 'bottom-right', 10, 10);
            }

            // حفظ الصورة في public/uploads/...
            $image->save($filePath);

            // إنشاء الصورة المصغرة
            if ($generateThumbnail) {
                // We save originals directly under public/uploads/... so thumbnails must be there too.
                $this->generateThumbnail($sourcePath, $folderPath, $fileName, true, $topCropPx);
            }

            // حفظ في قاعدة البيانات
            if ($relation && method_exists($model, $relation)) {
                $model->$relation()->create([
                    'file_name' => $fileName,
                    'disk' => 'direct_public',
                    'mediable_id' => $model->id,
                    'mediable_type' => get_class($model),
                    'collection_name' => $collectionName ?? 'gallery',
                ]);
            }

            $uploadedFiles[] = $fileName;
        }

        return $uploadedFiles;
    }

    /*public function getMultipleMediaUrls(
        string $baseFolder,
        $model,
        ?string $relation = null,
        ?string $collectionName = null,
        bool $useStorage = false
    ): array {
        $images = [];

        // لو مفيش موديل نخرج
        if (!$model) {
            return [];
        }

        $base = "uploads/$baseFolder";

        // لو فيه relation
        if ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();

            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }

            $mediaItems = $query->get();

            foreach ($mediaItems as $media) {
                $fileName = $media->file_name;
                $disk = $media->disk;

                if ($disk === 'direct_public') {
                    $images[] = [
                        'original' => asset("{$base}/{$fileName}"),
                        'thumbnail' => asset("{$base}/thumbnails/{$fileName}"),
                    ];
                } elseif ($disk === 'storage_public') {
                    $images[] = [
                        'original' => asset("storage/{$base}/{$fileName}"),
                        'thumbnail' => asset("storage/{$base}/thumbnails/{$fileName}"),
                    ];
                }
            }
        }

        return $images;
    }*/
    public function getMultipleMediaUrls(
        string $baseFolder,
        $model,
        ?string $relation = null,
        ?string $collectionName = null
    ): array {
        $images = [];

        // لو مفيش موديل نخرج
        if (!$model) {
            return [];
        }

        $uploadsBase = "uploads/$baseFolder";

        // لو فيه relation
        if ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();

            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }

            $mediaItems = $query->get();

            foreach ($mediaItems as $media) {
                $fileName = $media->file_name;
                $disk = (string) ($media->disk ?? 'direct_public');

                $images[] = [
                    'original'   => $this->publicUploadsUrl($disk, "{$uploadsBase}/{$fileName}"),
                    'thumbnail'  => $this->publicUploadsUrl($disk, "{$uploadsBase}/thumbnails/{$fileName}"),
                ];
            }
        }

        return $images;
    }

    private function applyTopCrop($image, int $topCropPx): void
    {
        $crop = max(0, (int) $topCropPx);
        if ($crop === 0) {
            return;
        }

        $width = (int) $image->width();
        $height = (int) $image->height();
        if ($width <= 0 || $height <= 1) {
            return;
        }

        // Keep coordinates valid and avoid over-cropping on very short images.
        $crop = min($crop, $height - 1);
        $image->crop($width, $height - $crop, 0, $crop);
    }

    private function applyTopRightNameBlur($image, bool $enabled, ?int $blurStrength = null): void
    {
        if (! $enabled) {
            return;
        }

        $imageWidth = (int) $image->width();
        $imageHeight = (int) $image->height();
        if ($imageWidth <= 1 || $imageHeight <= 1) {
            return;
        }

        $mode = strtolower((string) config('account_image.name_blur.mode', 'adaptive'));
        if ($mode === 'adaptive') {
            $offsetRatio = (float) config('account_image.name_blur.x_offset_from_right_ratio', 0.39);
            $yRatio = (float) config('account_image.name_blur.y_ratio', 0.037);
            $wRatio = (float) config('account_image.name_blur.width_ratio', 0.325);
            $hRatio = (float) config('account_image.name_blur.height_ratio', 0.093);

            $offsetPx = (int) round($imageWidth * max(0.0, min(1.0, $offsetRatio)));
            $x = max(0, $imageWidth - $offsetPx);
            $y = (int) round($imageHeight * max(0.0, min(1.0, $yRatio)));
            $w = (int) round($imageWidth * max(0.01, min(1.0, $wRatio)));
            $h = (int) round($imageHeight * max(0.01, min(1.0, $hRatio)));
        } else {
            $offsetFromRight = (int) config('account_image.name_blur.x_offset_from_right', 420);
            $x = max(0, $imageWidth - $offsetFromRight);
            $y = max(0, (int) config('account_image.name_blur.y', 40));
            $w = max(1, (int) config('account_image.name_blur.width', 350));
            $h = max(1, (int) config('account_image.name_blur.height', 100));
        }
        $strength = $blurStrength ?? (int) config('account_image.name_blur.strength', 35);

        if ($y >= $imageHeight) {
            return;
        }

        $w = min($w, $imageWidth - $x);
        $h = min($h, $imageHeight - $y);
        if ($w <= 0 || $h <= 0) {
            return;
        }

        $region = clone $image;
        $region->crop($w, $h, $x, $y);
        $region->blur(max(1, min(100, (int) $strength)));
        $image->insert($region, 'top-left', $x, $y);
    }
}
