<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

trait UploadMedia2 {
    private function appImageBlurSettings(): array
    {
        static $cached = null;
        if (is_array($cached)) {
            return $cached;
        }

        $cfgCenterX = config('account_image.center_blur.x', null);
        $cfgCenterY = config('account_image.center_blur.y', null);
        $useDashboardControls = (bool) config('account_image.use_dashboard_controls', true);
        $defaults = [
            'top_area_mode' => (string) config('account_image.top_area.mode', 'blur'),
            'top_area_size_px' => (int) config('account_image.top_area.size_px', 35),
            'top_area_blur_strength' => (int) config('account_image.top_area.blur_strength', 35),
            'top_area_width_px' => (int) config('account_image.top_area.width_px', 0),
            'top_area_x_from_right_px' => (int) config('account_image.top_area.x_from_right_px', 0),
            'name_blur_enabled' => (bool) config('account_image.name_blur.enabled', true),
            'name_blur_mode' => (string) config('account_image.name_blur.mode', 'adaptive'),
            'name_blur_x_offset_from_right' => (int) config('account_image.name_blur.x_offset_from_right', 420),
            'name_blur_y' => (int) config('account_image.name_blur.y', 40),
            'name_blur_width' => (int) config('account_image.name_blur.width', 350),
            'name_blur_height' => (int) config('account_image.name_blur.height', 100),
            'name_blur_strength' => (int) config('account_image.name_blur.strength', 35),
            'center_blur_enabled' => (bool) config('account_image.center_blur.enabled', false),
            'center_blur_mode' => (string) config('account_image.center_blur.mode', 'fixed'),
            // null/0 means "auto center".
            'center_blur_x' => ($cfgCenterX === '' ? null : $cfgCenterX),
            'center_blur_y' => ($cfgCenterY === '' ? null : $cfgCenterY),
            'center_blur_x_from_right' => (int) config('account_image.center_blur.x_from_right', 0),
            'center_blur_width' => (int) config('account_image.center_blur.width', 120),
            'center_blur_height' => (int) config('account_image.center_blur.height', 120),
            'center_blur_x_ratio' => (float) config('account_image.center_blur.x_ratio', 0.5),
            'center_blur_y_ratio' => (float) config('account_image.center_blur.y_ratio', 0.5),
            'center_blur_width_ratio' => (float) config('account_image.center_blur.width_ratio', 0.2),
            'center_blur_height_ratio' => (float) config('account_image.center_blur.height_ratio', 0.2),
            'center_blur_strength' => (int) config('account_image.center_blur.strength', 35),
            'publish_banner_privacy_blur_enabled' => (bool) config('account_image.publish_banner_privacy_blur.enabled', true),
            'publish_banner_privacy_blur_mode' => (string) config('account_image.publish_banner_privacy_blur.mode', 'dual'),
            'publish_banner_privacy_blur_strength' => (int) config('account_image.publish_banner_privacy_blur.strength', 55),
            'publish_banner_name_x_ratio' => (float) config('account_image.publish_banner_privacy_blur.name_x_ratio', 0.72),
            'publish_banner_name_y_ratio' => (float) config('account_image.publish_banner_privacy_blur.name_y_ratio', 0.17),
            'publish_banner_name_width_ratio' => (float) config('account_image.publish_banner_privacy_blur.name_width_ratio', 0.24),
            'publish_banner_name_height_ratio' => (float) config('account_image.publish_banner_privacy_blur.name_height_ratio', 0.10),
            'publish_banner_uid_x_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid_x_ratio', 0.80),
            'publish_banner_uid_y_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid_y_ratio', 0.28),
            'publish_banner_uid_width_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid_width_ratio', 0.17),
            'publish_banner_uid_height_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid_height_ratio', 0.08),
            'publish_banner_alt_name_x_ratio' => (float) config('account_image.publish_banner_privacy_blur.name2_x_ratio', 0.60),
            'publish_banner_alt_name_y_ratio' => (float) config('account_image.publish_banner_privacy_blur.name2_y_ratio', 0.14),
            'publish_banner_alt_name_width_ratio' => (float) config('account_image.publish_banner_privacy_blur.name2_width_ratio', 0.23),
            'publish_banner_alt_name_height_ratio' => (float) config('account_image.publish_banner_privacy_blur.name2_height_ratio', 0.09),
            'publish_banner_alt_uid_x_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid2_x_ratio', 0.67),
            'publish_banner_alt_uid_y_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid2_y_ratio', 0.24),
            'publish_banner_alt_uid_width_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid2_width_ratio', 0.16),
            'publish_banner_alt_uid_height_ratio' => (float) config('account_image.publish_banner_privacy_blur.uid2_height_ratio', 0.07),
            'publish_banner_card_x_ratio' => (float) config('account_image.publish_banner_privacy_blur.card_x_ratio', 0.66),
            'publish_banner_card_y_ratio' => (float) config('account_image.publish_banner_privacy_blur.card_y_ratio', 0.10),
            'publish_banner_card_width_ratio' => (float) config('account_image.publish_banner_privacy_blur.card_width_ratio', 0.31),
            'publish_banner_card_height_ratio' => (float) config('account_image.publish_banner_privacy_blur.card_height_ratio', 0.30),
        ];

        try {
            if ($useDashboardControls && class_exists(\App\Models\Setting::class)) {
                $setting = \Illuminate\Support\Facades\Cache::remember('app_settings', 60 * 10, function () {
                    return \App\Models\Setting::query()->latest('id')->first();
                });
                if ($setting) {
                    $defaults['top_area_mode'] = (string) ($setting->account_top_area_mode ?? $defaults['top_area_mode']);
                    $defaults['top_area_size_px'] = max(0, (int) ($setting->account_top_area_size_px ?? $defaults['top_area_size_px']));
                    $defaults['top_area_blur_strength'] = max(1, min(100, (int) ($setting->account_top_area_blur_strength ?? $defaults['top_area_blur_strength'])));
                    $defaults['top_area_width_px'] = max(0, (int) ($setting->account_top_area_width_px ?? $defaults['top_area_width_px']));
                    $defaults['top_area_x_from_right_px'] = max(0, (int) ($setting->account_top_area_x_from_right_px ?? $defaults['top_area_x_from_right_px']));

                    $defaults['name_blur_enabled'] = (bool) ($setting->account_name_blur_enabled ?? $defaults['name_blur_enabled']);
                    $defaults['name_blur_mode'] = (string) ($setting->account_name_blur_mode ?? $defaults['name_blur_mode']);
                    $defaults['name_blur_x_offset_from_right'] = (int) ($setting->account_name_blur_x_offset_from_right ?? $defaults['name_blur_x_offset_from_right']);
                    $defaults['name_blur_y'] = (int) ($setting->account_name_blur_y ?? $defaults['name_blur_y']);
                    $defaults['name_blur_width'] = (int) ($setting->account_name_blur_width ?? $defaults['name_blur_width']);
                    $defaults['name_blur_height'] = (int) ($setting->account_name_blur_height ?? $defaults['name_blur_height']);
                    $defaults['name_blur_strength'] = (int) ($setting->account_name_blur_strength ?? $defaults['name_blur_strength']);

                    $defaults['center_blur_enabled'] = (bool) ($setting->account_center_blur_enabled ?? $defaults['center_blur_enabled']);
                    $defaults['center_blur_x'] = $setting->account_center_blur_x ?? $defaults['center_blur_x'];
                    $defaults['center_blur_y'] = $setting->account_center_blur_y ?? $defaults['center_blur_y'];
                    $defaults['center_blur_x_from_right'] = (int) ($setting->account_center_blur_x_from_right ?? $defaults['center_blur_x_from_right']);
                    $defaults['center_blur_width'] = (int) ($setting->account_center_blur_width ?? $defaults['center_blur_width']);
                    $defaults['center_blur_height'] = (int) ($setting->account_center_blur_height ?? $defaults['center_blur_height']);
                    $defaults['center_blur_strength'] = (int) ($setting->account_center_blur_strength ?? $defaults['center_blur_strength']);
                }
            }
        } catch (\Throwable $e) {
            // Use defaults from config on any failure.
        }

        $cached = $defaults;
        return $cached;
    }
    private function normalizeImageExtension(string $extension): string
    {
        $ext = strtolower(trim($extension));
        if ($ext === 'jpeg' || $ext === 'jfif' || $ext === 'pjpeg') {
            return 'jpg';
        }
        if ($ext === '') {
            return 'jpg';
        }
        return $ext;
    }

    private function webPublicPrefix(): string
    {
        try {
            $docRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
            $publicRoot = realpath(public_path());
            if (!$docRoot || !$publicRoot) {
                return '';
            }

            $docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
            $publicRoot = rtrim(str_replace('\\', '/', $publicRoot), '/');

            if ($docRoot === $publicRoot) {
                return '';
            }

            $prefixProbe = $docRoot . '/';
            if (str_starts_with($publicRoot, $prefixProbe)) {
                return trim(substr($publicRoot, strlen($prefixProbe)), '/');
            }
        } catch (\Throwable $e) {
            // ignore and fallback to empty prefix
        }

        return '';
    }

    private function withPublicPrefix(string $path): string
    {
        $path = ltrim($path, '/');
        $prefix = $this->webPublicPrefix();
        if ($prefix === '') {
            return $path;
        }
        if (str_starts_with($path, $prefix . '/')) {
            return $path;
        }
        return "{$prefix}/{$path}";
    }

    private function resolveStoredPath(string $uploadsBase, string $fileName): string
    {
        $fileName = trim((string) $fileName);
        if ($fileName === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $fileName)) {
            return $fileName;
        }

        $fileName = str_replace('\\', '/', $fileName);
        $fileName = ltrim($fileName, '/');

        // Legacy rows may already contain full relative path.
        if (str_starts_with($fileName, 'uploads/') || str_starts_with($fileName, 'storage/uploads/')) {
            return $fileName;
        }

        return trim($uploadsBase, '/') . '/' . basename($fileName);
    }

    private function fileExistsOnPublicOrStorage(string $uploadsPath): bool
    {
        if (preg_match('#^https?://#i', $uploadsPath)) {
            return true;
        }

        $uploadsPath = ltrim((string) $uploadsPath, '/');
        if ($uploadsPath === '') {
            return false;
        }

        $directFilePath = public_path($uploadsPath);
        $storageRelative = str_starts_with($uploadsPath, 'storage/')
            ? ltrim((string) preg_replace('#^storage/#', '', $uploadsPath), '/')
            : $uploadsPath;
        $storageFilePath = storage_path("app/public/{$storageRelative}");

        return is_file($directFilePath) || is_file($storageFilePath);
    }

    private function publicUploadsUrl(string $disk, string $uploadsPath): string
    {
        if (preg_match('#^https?://#i', $uploadsPath)) {
            return $uploadsPath;
        }

        $uploadsPath = ltrim($uploadsPath, '/');
        $disk = strtolower(trim($disk));

        $directRelative = $uploadsPath;
        $storageRelative = str_starts_with($uploadsPath, 'storage/')
            ? $uploadsPath
            : "storage/{$uploadsPath}";
        $storageInternal = ltrim(preg_replace('#^storage/#', '', $storageRelative), '/');

        $directFilePath = public_path($directRelative);
        $storageFilePath = storage_path("app/public/{$storageInternal}");
        $directUrl = asset($this->withPublicPrefix($directRelative));
        $storageUrl = asset($this->withPublicPrefix($storageRelative));

        if ($disk === 'storage_public') {
            // Prefer storage path, then fallback to direct public if legacy/migrated files are mixed.
            if (is_file($storageFilePath)) {
                return $storageUrl;
            }
            if (is_file($directFilePath)) {
                return $directUrl;
            }
            return '';
        }

        if ($disk === 'direct_public') {
            // Prefer direct public path, then fallback to storage path if needed.
            if (is_file($directFilePath)) {
                return $directUrl;
            }
            if (is_file($storageFilePath)) {
                return $storageUrl;
            }
            return '';
        }

        // Unknown/empty disk from legacy rows: auto-detect existing file location.
        if (is_file($directFilePath)) {
            return $directUrl;
        }
        if (is_file($storageFilePath)) {
            return $storageUrl;
        }

        return '';
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
        $extension = $this->normalizeImageExtension((string) $file->getClientOriginalExtension());
        $fileName = uniqid() . '.' . $extension;
        $filePath = "$folderPath/$fileName";
        $sourcePath = $file->getPathname();
        $settings = $this->appImageBlurSettings();
        $topCropPx = (int) ($settings['top_area_size_px'] ?? $topCropPx);
        if ($topCropPx < 0) {
            $topCropPx = 0;
        }
        $topMaskMode = strtolower((string) ($settings['top_area_mode'] ?? config('account_image.top_area.mode', 'crop')));
        $image = Image::make($sourcePath);
        $this->applyTopMask($image, $topCropPx, $topMaskMode);
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
            $mediaItems = $query->get();
            foreach ($mediaItems as $media) {
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
        $settings = $this->appImageBlurSettings();
        $topCropPx = (int) ($settings['top_area_size_px'] ?? $topCropPx);
        if ($topCropPx < 0) $topCropPx = 0;
        $topMaskMode = strtolower((string) ($settings['top_area_mode'] ?? config('account_image.top_area.mode', 'crop')));
        // Always process thumbnail on a separate instance from original image.
        $thumbnail = Image::make($sourcePath);
        $this->applyTopMask($thumbnail, $topCropPx, $topMaskMode);
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
                $path = $this->resolveStoredPath($base, (string) $fileName);
                $thumb = $this->resolveStoredPath("{$base}/thumbnails", (string) $fileName);
                $images['original'] = $this->publicUploadsUrl('direct_public', $path);
                $images['thumbnail'] = $this->publicUploadsUrl('direct_public', $thumb);
            }
        } elseif ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();
            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }
            $mediaItems = $query->orderByDesc('id')->get();
            if ($mediaItems->isEmpty() && $collectionName) {
                // Backward compatibility: only fallback to legacy unscoped rows
                // (empty/default collection), never to other named collections.
                $legacyQuery = $model->$relation();
                $legacyQuery->where(function ($q) {
                    $q->whereNull('collection_name')
                        ->orWhere('collection_name', '')
                        ->orWhere('collection_name', 'default');
                });
                $mediaItems = $legacyQuery->orderByDesc('id')->get();
            }

            $selected = null;
            foreach ($mediaItems as $item) {
                $candidatePath = $this->resolveStoredPath($base, (string) ($item->file_name ?? ''));
                if ($this->fileExistsOnPublicOrStorage($candidatePath)) {
                    $selected = $item;
                    break;
                }
            }
            if (! $selected && $mediaItems->isNotEmpty()) {
                $selected = $mediaItems->first();
            }

            if ($selected) {
                $disk = (string) ($selected->disk ?? '');
                $fileName = (string) ($selected->file_name ?? '');
                $path = $this->resolveStoredPath($base, $fileName);
                $thumb = $this->resolveStoredPath("{$base}/thumbnails", $fileName);
                $images['original'] = $this->publicUploadsUrl($disk, $path);
                $images['thumbnail'] = $this->publicUploadsUrl($disk, $thumb);
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
                $path = $this->resolveStoredPath($uploadsBase, (string) $fileName);
                $url = $this->publicUploadsUrl('direct_public', $path);
                return $url !== '' ? $url : null;
            }
        }
        if ($relation && method_exists($model, $relation)) {
            $query = $model->$relation();
            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }
            $mediaItems = $query->orderByDesc('id')->get();
            if ($mediaItems->isEmpty() && $collectionName) {
                // Backward compatibility: only fallback to legacy unscoped rows
                // (empty/default collection), never to other named collections.
                $legacyQuery = $model->$relation();
                $legacyQuery->where(function ($q) {
                    $q->whereNull('collection_name')
                        ->orWhere('collection_name', '')
                        ->orWhere('collection_name', 'default');
                });
                $mediaItems = $legacyQuery->orderByDesc('id')->get();
            }

            $selected = null;
            foreach ($mediaItems as $item) {
                $candidatePath = $this->resolveStoredPath($uploadsBase, (string) ($item->file_name ?? ''));
                if ($this->fileExistsOnPublicOrStorage($candidatePath)) {
                    $selected = $item;
                    break;
                }
            }
            if (! $selected && $mediaItems->isNotEmpty()) {
                $selected = $mediaItems->first();
            }

            if ($selected) {
                $fileName = (string) ($selected->file_name ?? '');
                $disk = (string) ($selected->disk ?? '');
                $path = $this->resolveStoredPath($uploadsBase, $fileName);
                $url = $this->publicUploadsUrl($disk, $path);
                return $url !== '' ? $url : null;
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
        foreach ($files as $index => $file) {
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
        $isPublicPublish = (string) ($model->publish_source ?? '') === 'public';

        // المسار داخل public مباشرة
        $folderPath = "uploads/$baseFolder";
        $fullPath = public_path($folderPath);

        // إنشاء المجلد إذا مش موجود
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile || !$this->isValidImage($file)) {
                continue;
            }

            $extension = $this->normalizeImageExtension((string) $file->getClientOriginalExtension());
            $fileName = uniqid() . '.' . $extension;
            $filePath = $fullPath . '/' . $fileName;
            $sourcePath = $file->getPathname();
            $image = Image::make($sourcePath);
            $settings = $this->appImageBlurSettings();
            $topCropPx = (int) ($settings['top_area_size_px'] ?? $topCropPx);
            if ($topCropPx < 0) $topCropPx = 0;
            $topMaskMode = strtolower((string) ($settings['top_area_mode'] ?? config('account_image.top_area.mode', 'crop')));
            $this->applyTopMask($image, $topCropPx, $topMaskMode);
            // For public publish-product image #9 (banners slot), blur only sensitive areas:
            // account name + UID (instead of a large generic center blur).
            // Guided order maps slot #9 to zero-based index 8.
            if ($isPublicPublish && (int) $index === 8) {
                $this->applyPublishBannerPrivacyBlur($image);
            }

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
            if ($mediaItems->isEmpty() && $collectionName) {
                // Backward compatibility: only fallback to legacy unscoped rows
                // (empty/default collection), never to other named collections.
                $legacyQuery = $model->$relation();
                $legacyQuery->where(function ($q) {
                    $q->whereNull('collection_name')
                        ->orWhere('collection_name', '')
                        ->orWhere('collection_name', 'default');
                });
                $mediaItems = $legacyQuery->get();
            }

            foreach ($mediaItems as $media) {
                $fileName = $media->file_name;
                $disk = (string) ($media->disk ?? 'direct_public');
                $path = $this->resolveStoredPath($uploadsBase, (string) $fileName);
                $thumb = $this->resolveStoredPath("{$uploadsBase}/thumbnails", (string) $fileName);

                $images[] = [
                    'original'   => $this->publicUploadsUrl($disk, $path),
                    'thumbnail'  => $this->publicUploadsUrl($disk, $thumb),
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

    private function applyTopMask($image, int $topCropPx, string $mode = 'crop'): void
    {
        $px = max(0, (int) $topCropPx);
        if ($px === 0) {
            return;
        }

        $mode = strtolower(trim((string) $mode));
        if ($mode === 'none') {
            return;
        }

        if ($mode === 'blur') {
            $this->applyTopStripBlur($image, $px);
            return;
        }

        // Default/fallback: crop.
        $this->applyTopCrop($image, $px);
    }

    private function applyTopStripBlur($image, int $topPx, ?int $strength = null): void
    {
        $imageWidth = (int) $image->width();
        $imageHeight = (int) $image->height();
        if ($imageWidth <= 1 || $imageHeight <= 1) {
            return;
        }

        $h = min(max(1, $topPx), $imageHeight);
        $settings = $this->appImageBlurSettings();
        $s = $strength ?? (int) ($settings['top_area_blur_strength'] ?? config('account_image.top_area.blur_strength', 35));
        $wPx = (int) ($settings['top_area_width_px'] ?? config('account_image.top_area.width_px', 0));
        $wRatio = (float) config('account_image.top_area.width_ratio', 1.0);
        $xFromRight = (int) ($settings['top_area_x_from_right_px'] ?? config('account_image.top_area.x_from_right_px', 0));

        $w = $wPx > 0
            ? min($imageWidth, $wPx)
            : (int) round($imageWidth * max(0.01, min(1.0, $wRatio)));
        $xFromRight = max(0, $xFromRight);
        $x = max(0, $imageWidth - $xFromRight - $w);
        $w = min($w, $imageWidth - $x);
        if ($w <= 0) {
            return;
        }

        $strip = clone $image;
        $strip->crop($w, $h, $x, 0);
        $strip->blur(max(1, min(100, (int) $s)));
        $image->insert($strip, 'top-left', $x, 0);
    }

    private function applyTopRightNameBlur($image, bool $enabled, ?int $blurStrength = null): void
    {
        $settings = $this->appImageBlurSettings();
        $effectiveEnabled = $enabled && (bool) ($settings['name_blur_enabled'] ?? true);
        if (! $effectiveEnabled) {
            return;
        }

        $imageWidth = (int) $image->width();
        $imageHeight = (int) $image->height();
        if ($imageWidth <= 1 || $imageHeight <= 1) {
            return;
        }

        $mode = strtolower((string) ($settings['name_blur_mode'] ?? config('account_image.name_blur.mode', 'adaptive')));
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
            $offsetFromRight = (int) ($settings['name_blur_x_offset_from_right'] ?? config('account_image.name_blur.x_offset_from_right', 420));
            $x = max(0, $imageWidth - $offsetFromRight);
            $y = max(0, (int) ($settings['name_blur_y'] ?? config('account_image.name_blur.y', 40)));
            $w = max(1, (int) ($settings['name_blur_width'] ?? config('account_image.name_blur.width', 350)));
            $h = max(1, (int) ($settings['name_blur_height'] ?? config('account_image.name_blur.height', 100)));
        }
        $strength = $blurStrength ?? (int) ($settings['name_blur_strength'] ?? config('account_image.name_blur.strength', 35));

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

    private function applyCenterSmallBlur($image): void
    {
        $settings = $this->appImageBlurSettings();
        if (! (bool) ($settings['center_blur_enabled'] ?? false)) {
            return;
        }

        $imageWidth = (int) $image->width();
        $imageHeight = (int) $image->height();
        if ($imageWidth <= 1 || $imageHeight <= 1) {
            return;
        }

        $w = max(1, (int) ($settings['center_blur_width'] ?? 140));
        $h = max(1, (int) ($settings['center_blur_height'] ?? 140));
        $strength = (int) ($settings['center_blur_strength'] ?? 35);
        $xRaw = $settings['center_blur_x'] ?? null;
        $yRaw = $settings['center_blur_y'] ?? null;
        $xFromRightRaw = (int) ($settings['center_blur_x_from_right'] ?? 0);
        $mode = strtolower((string) ($settings['center_blur_mode'] ?? 'fixed'));

        if ($mode === 'adaptive') {
            $xRatio = max(0.0, min(1.0, (float) ($settings['center_blur_x_ratio'] ?? 0.5)));
            $yRatio = max(0.0, min(1.0, (float) ($settings['center_blur_y_ratio'] ?? 0.5)));
            $wRatio = max(0.01, min(1.0, (float) ($settings['center_blur_width_ratio'] ?? 0.2)));
            $hRatio = max(0.01, min(1.0, (float) ($settings['center_blur_height_ratio'] ?? 0.2)));

            $w = max(1, (int) round($imageWidth * $wRatio));
            $h = max(1, (int) round($imageHeight * $hRatio));
            // Treat x/y ratios as center points for easier "put it in middle" behavior.
            $x = max(0, (int) round(($imageWidth * $xRatio) - ($w / 2)));
            $y = max(0, (int) round(($imageHeight * $yRatio) - ($h / 2)));
        } else {

            // Priority: right offset (if > 0), then absolute X, then auto-center.
            if ($xFromRightRaw > 0) {
                $x = max(0, $imageWidth - $xFromRightRaw - $w);
            } else {
                $x = ($xRaw === null || (int) $xRaw === 0)
                    ? max(0, (int) floor(($imageWidth - $w) / 2))
                    : max(0, (int) $xRaw);
            }
            $y = ($yRaw === null || (int) $yRaw === 0)
                ? max(0, (int) floor(($imageHeight - $h) / 2))
                : max(0, (int) $yRaw);
        }

        if ($x >= $imageWidth || $y >= $imageHeight) {
            return;
        }
        $w = min($w, $imageWidth - $x);
        $h = min($h, $imageHeight - $y);
        if ($w <= 0 || $h <= 0) {
            return;
        }

        $region = clone $image;
        $region->crop($w, $h, $x, $y);
        $region->blur(max(1, min(100, $strength)));
        $image->insert($region, 'top-left', $x, $y);
    }

    /**
     * Precise blur for publish-product banner slot:
     * - Account name region
     * - UID region
     * Controlled from .env via account_image.banner_target_blur.*.
     */
    private function applyBannerTargetsBlur($image): void
    {
        $targets = (array) config('account_image.banner_target_blur', []);
        $enabled = (bool) ($targets['enabled'] ?? false);
        if (! $enabled) {
            return;
        }

        $imageWidth = (int) $image->width();
        $imageHeight = (int) $image->height();
        if ($imageWidth <= 1 || $imageHeight <= 1) {
            return;
        }

        $strength = (int) ($targets['strength'] ?? 80);
        $name = (array) ($targets['name'] ?? []);
        $uid = (array) ($targets['uid'] ?? []);

        $this->applyRatioBlurRegion($image, $name, $imageWidth, $imageHeight, $strength);
        $this->applyRatioBlurRegion($image, $uid, $imageWidth, $imageHeight, $strength);
    }

    private function applyRatioBlurRegion($image, array $cfg, int $imageWidth, int $imageHeight, int $strength): void
    {
        $xRatio = max(0.0, min(1.0, (float) ($cfg['x_ratio'] ?? 0.0)));
        $yRatio = max(0.0, min(1.0, (float) ($cfg['y_ratio'] ?? 0.0)));
        $wRatio = max(0.0, min(1.0, (float) ($cfg['width_ratio'] ?? 0.0)));
        $hRatio = max(0.0, min(1.0, (float) ($cfg['height_ratio'] ?? 0.0)));
        if ($wRatio <= 0.0 || $hRatio <= 0.0) {
            return;
        }

        $x = (int) round($imageWidth * $xRatio);
        $y = (int) round($imageHeight * $yRatio);
        $w = max(1, (int) round($imageWidth * $wRatio));
        $h = max(1, (int) round($imageHeight * $hRatio));

        if ($x >= $imageWidth || $y >= $imageHeight) {
            return;
        }
        $w = min($w, $imageWidth - $x);
        $h = min($h, $imageHeight - $y);
        if ($w <= 0 || $h <= 0) {
            return;
        }

        $region = clone $image;
        $region->crop($w, $h, $x, $y);
        $region->blur(max(1, min(100, $strength)));
        $image->insert($region, 'top-left', $x, $y);
    }

    private function applyPublishBannerPrivacyBlur($image): void
    {
        $settings = $this->appImageBlurSettings();
        if (! (bool) ($settings['publish_banner_privacy_blur_enabled'] ?? true)) {
            return;
        }

        $strength = (int) ($settings['publish_banner_privacy_blur_strength'] ?? 55);
        $mode = strtolower((string) ($settings['publish_banner_privacy_blur_mode'] ?? 'dual'));

        // Primary regions (layout #1)
        $regions = [
            [
                'x' => (float) ($settings['publish_banner_name_x_ratio'] ?? 0.72),
                'y' => (float) ($settings['publish_banner_name_y_ratio'] ?? 0.17),
                'w' => (float) ($settings['publish_banner_name_width_ratio'] ?? 0.24),
                'h' => (float) ($settings['publish_banner_name_height_ratio'] ?? 0.10),
            ],
            [
                'x' => (float) ($settings['publish_banner_uid_x_ratio'] ?? 0.80),
                'y' => (float) ($settings['publish_banner_uid_y_ratio'] ?? 0.28),
                'w' => (float) ($settings['publish_banner_uid_width_ratio'] ?? 0.17),
                'h' => (float) ($settings['publish_banner_uid_height_ratio'] ?? 0.08),
            ],
        ];

        // Secondary regions (layout #2 fallback)
        if (in_array($mode, ['dual', 'safe'], true)) {
            $regions[] = [
                'x' => (float) ($settings['publish_banner_alt_name_x_ratio'] ?? 0.69),
                'y' => (float) ($settings['publish_banner_alt_name_y_ratio'] ?? 0.20),
                'w' => (float) ($settings['publish_banner_alt_name_width_ratio'] ?? 0.26),
                'h' => (float) ($settings['publish_banner_alt_name_height_ratio'] ?? 0.11),
            ];
            $regions[] = [
                'x' => (float) ($settings['publish_banner_alt_uid_x_ratio'] ?? 0.78),
                'y' => (float) ($settings['publish_banner_alt_uid_y_ratio'] ?? 0.30),
                'w' => (float) ($settings['publish_banner_alt_uid_width_ratio'] ?? 0.20),
                'h' => (float) ($settings['publish_banner_alt_uid_height_ratio'] ?? 0.09),
            ];
        }

        foreach ($regions as $r) {
            $this->applyRatioBlurBox(
                $image,
                (float) ($r['x'] ?? 0.0),
                (float) ($r['y'] ?? 0.0),
                (float) ($r['w'] ?? 0.0),
                (float) ($r['h'] ?? 0.0),
                $strength
            );
        }

        // Safe mode: blur the whole profile card area (strong privacy fallback).
        if ($mode === 'safe') {
            $this->applyRatioBlurBox(
                $image,
                (float) ($settings['publish_banner_card_x_ratio'] ?? 0.66),
                (float) ($settings['publish_banner_card_y_ratio'] ?? 0.10),
                (float) ($settings['publish_banner_card_width_ratio'] ?? 0.31),
                (float) ($settings['publish_banner_card_height_ratio'] ?? 0.30),
                max(30, $strength)
            );
        }
    }

    private function applyRatioBlurBox($image, float $xRatio, float $yRatio, float $wRatio, float $hRatio, int $strength): void
    {
        $imageWidth = (int) $image->width();
        $imageHeight = (int) $image->height();
        if ($imageWidth <= 1 || $imageHeight <= 1) {
            return;
        }

        $xRatio = max(0.0, min(1.0, $xRatio));
        $yRatio = max(0.0, min(1.0, $yRatio));
        $wRatio = max(0.01, min(1.0, $wRatio));
        $hRatio = max(0.01, min(1.0, $hRatio));

        $x = (int) round($imageWidth * $xRatio);
        $y = (int) round($imageHeight * $yRatio);
        $w = (int) round($imageWidth * $wRatio);
        $h = (int) round($imageHeight * $hRatio);

        if ($x >= $imageWidth || $y >= $imageHeight) {
            return;
        }
        $w = min(max(1, $w), $imageWidth - $x);
        $h = min(max(1, $h), $imageHeight - $y);
        if ($w <= 0 || $h <= 0) {
            return;
        }

        $region = clone $image;
        $region->crop($w, $h, $x, $y);
        $region->blur(max(1, min(100, $strength)));
        $image->insert($region, 'top-left', $x, $y);
    }
}
