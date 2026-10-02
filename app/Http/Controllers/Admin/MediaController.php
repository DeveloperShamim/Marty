<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /** Folders of the public disk (storage/app/public) with shop images: banners, logo, categories, brands. */
    private const STORAGE_FOLDERS = [
        'banners' => 'banners',
        'branding' => 'branding',
        'categories' => 'categories',
        'brands/logos' => 'branding',
        'brands/banners' => 'branding',
    ];

    public function index(Request $request)
    {
        $filterType = $request->query('type', 'all');
        $search = strtolower(trim((string) $request->query('search', '')));

        // 1. Gather database usage references
        $productImages = ProductImage::with('product')->get()->keyBy(function ($img) {
            return $this->refKey($img->path);
        });

        $categories = Category::all();
        $categoryImages = [];
        foreach ($categories as $c) {
            if ($c->image) {
                $categoryImages[$this->refKey($c->image)] = $c->name;
            }
        }

        $brands = Brand::all();
        $brandImages = [];
        foreach ($brands as $b) {
            if ($b->logo) $brandImages[$this->refKey($b->logo)] = "Brand Logo: " . $b->name;
            if ($b->banner) $brandImages[$this->refKey($b->banner)] = "Brand Banner: " . $b->name;
        }

        $banners = Banner::all();
        $bannerImages = [];
        foreach ($banners as $bn) {
            if ($bn->image) $bannerImages[$this->refKey($bn->image)] = "Hero Banner: " . ($bn->title ?: 'Banner #' . $bn->id);
        }

        $logoPath = $this->refKey(setting('logo'));
        $faviconPath = $this->refKey(setting('favicon'));

        // 2. Scan public upload directories
        $directories = [
            public_path('uploads/products') => 'products',
            public_path('uploads/media')    => 'media',
            public_path('uploads')          => 'branding',
            public_path('storage')          => 'storage',
        ];
        foreach (self::STORAGE_FOLDERS as $folder => $category) {
            $directories[public_path('storage/' . $folder)] = $category;
        }

        $allFiles = [];
        $scannedPaths = [];

        foreach ($directories as $dirPath => $defaultCategory) {
            if (!File::isDirectory($dirPath)) {
                continue;
            }

            $files = File::files($dirPath);
            foreach ($files as $file) {
                $pathname = $file->getPathname();
                $normalizedPathname = str_replace('\\', '/', $pathname);

                if (in_array($normalizedPathname, $scannedPaths, true)) {
                    continue;
                }
                $scannedPaths[] = $normalizedPathname;

                $filename = $file->getFilename();
                $extension = strtolower($file->getExtension());
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
                    continue;
                }

                $relativePath = $this->normalizeRelPath($pathname);
                $url = asset($relativePath);
                $sizeBytes = $file->getSize();
                $lastModified = $file->getMTime();

                // Determine usage reference
                $usedBy = null;
                $categoryType = $defaultCategory;

                $altText = null;
                $colorTag = null;
                $dbImgId = null;

                if (isset($productImages[$relativePath])) {
                    $pImg = $productImages[$relativePath];
                    $usedBy = 'Product: ' . ($pImg->product?->name ?? 'Product #' . $pImg->product_id);
                    $categoryType = 'products';
                    $altText = $pImg->alt;
                    $colorTag = $pImg->color;
                    $dbImgId = $pImg->id;
                } elseif (isset($categoryImages[$relativePath])) {
                    $usedBy = 'Category: ' . $categoryImages[$relativePath];
                    $categoryType = 'categories';
                } elseif (isset($brandImages[$relativePath])) {
                    $usedBy = $brandImages[$relativePath];
                    $categoryType = 'branding';
                } elseif (isset($bannerImages[$relativePath])) {
                    $usedBy = $bannerImages[$relativePath];
                    $categoryType = 'banners';
                } elseif ($relativePath === $logoPath || $filename === 'logo.png') {
                    $usedBy = 'Site Main Logo';
                    $categoryType = 'branding';
                } elseif ($relativePath === $faviconPath || $filename === 'favicon.png') {
                    $usedBy = 'Site Favicon / App Icon';
                    $categoryType = 'branding';
                }

                // Check resolution dimensions if available
                $dimensions = null;
                if ($extension !== 'svg' && @getimagesize($pathname)) {
                    $info = @getimagesize($pathname);
                    if ($info) {
                        $dimensions = $info[0] . ' × ' . $info[1];
                    }
                }

                $item = [
                    'id' => md5($relativePath),
                    'db_id' => $dbImgId,
                    'filename' => $filename,
                    'relative_path' => $relativePath,
                    'absolute_path' => $pathname,
                    'url' => $url,
                    'size_bytes' => $sizeBytes,
                    'size_human' => $this->formatBytes($sizeBytes),
                    'dimensions' => $dimensions,
                    'extension' => strtoupper($extension),
                    'category' => $categoryType,
                    'used_by' => $usedBy,
                    'is_used' => !empty($usedBy),
                    'alt' => $altText,
                    'color' => $colorTag,
                    'modified_at' => \Illuminate\Support\Carbon::createFromTimestamp($lastModified),
                ];

                $allFiles[] = $item;
            }
        }

        // 3. Stats calculations
        $totalCount = count($allFiles);
        $totalBytes = array_sum(array_column($allFiles, 'size_bytes'));
        $unusedCount = count(array_filter($allFiles, fn($f) => !$f['is_used']));

        // 4. Apply Filters & Search
        $filtered = collect($allFiles);

        if ($filterType !== 'all') {
            if ($filterType === 'unused') {
                $filtered = $filtered->where('is_used', false);
            } else {
                $filtered = $filtered->where('category', $filterType);
            }
        }

        if ($search !== '') {
            $filtered = $filtered->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['filename']), $search)
                    || str_contains(strtolower($item['used_by'] ?? ''), $search);
            });
        }

        $items = $filtered->sortByDesc('modified_at')->values();

        return view('admin.media.index', [
            'items' => $items,
            'totalCount' => $totalCount,
            'totalBytesHuman' => $this->formatBytes($totalBytes),
            'unusedCount' => $unusedCount,
            'currentFilter' => $filterType,
            'currentSearch' => $search,
            'compressionQuality' => (int) setting('media_compression_quality', 80),
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:5120'],
        ]);

        $file = $request->file('image');
        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $targetDir = public_path('uploads/media');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0777, true, true);
        }

        $file->move($targetDir, $fileName);

        return back()->with('status', 'Image uploaded successfully to Media Library!');
    }

    public function saveQuality(Request $request)
    {
        $request->validate([
            'quality' => ['required', 'integer', 'min:10', 'max:100'],
        ]);

        $quality = max(10, min(100, (int) $request->input('quality')));
        Setting::updateOrCreate(['key' => 'media_compression_quality'], ['value' => (string) $quality]);
        Setting::forgetCache();

        return back()->with('status', "⚡ Default Image Compression Quality saved to {$quality}%!");
    }

    public function updateMetadata(Request $request)
    {
        $request->validate([
            'relative_path' => ['required', 'string'],
            'alt'           => ['nullable', 'string', 'max:500'],
            'color'         => ['nullable', 'string', 'max:255'],
        ]);

        $relPath = $this->normalizeRelPath($request->input('relative_path'));
        $alt = trim((string) $request->input('alt')) ?: null;
        $color = trim((string) $request->input('color')) ?: null;

        ProductImage::where('path', '/' . $relPath)
            ->orWhere('path', $relPath)
            ->update([
                'alt'   => $alt,
                'color' => $color,
            ]);

        return back()->with('status', '⚡ Image SEO metadata (Alt Text & Variation Tag) updated successfully!');
    }

    public function optimizeSingle(Request $request)
    {
        $request->validate([
            'relative_path' => ['required', 'string'],
        ]);

        $relPath = $this->normalizeRelPath($request->input('relative_path'));
        $absPath = $this->mediaAbsPath($relPath);

        if (!$absPath || !File::exists($absPath)) {
            return back()->withErrors(['image' => "Image file not found on disk: {$relPath}"]);
        }

        $quality = max(10, min(100, (int) $request->input('quality', setting('media_compression_quality', 80))));
        $result = $this->optimizeFile($absPath, $quality);

        if ($result['converted_webp']) {
            $msg = "⚡ Optimization & WebP Conversion complete (Quality: {$quality}%)! Converted image to WebP format (" . $this->formatBytes($result['initial_bytes']) . " → " . $this->formatBytes($result['final_bytes']) . ").";
        } elseif ($result['saved_bytes'] > 0) {
            $msg = "⚡ Optimization complete (Quality: {$quality}%)! Reduced file size by {$result['percent_saved']}% (" . $this->formatBytes($result['initial_bytes']) . " → " . $this->formatBytes($result['final_bytes']) . ").";
        } else {
            $msg = "Image is already fully optimized as WebP at {$quality}% quality! No further size reduction was needed.";
        }

        return back()->with('status', $msg);
    }

    public function bulkOptimize(Request $request)
    {
        $paths = $request->input('paths', []);
        $quality = max(10, min(100, (int) $request->input('quality', setting('media_compression_quality', 80))));

        // If no specific selection, optimize all images in public/uploads/products
        if (empty($paths)) {
            $dirPath = public_path('uploads/products');
            if (File::exists($dirPath)) {
                foreach (File::files($dirPath) as $f) {
                    $paths[] = $this->normalizeRelPath($f->getPathname());
                }
            }
        }

        $totalSavedBytes = 0;
        $optimizedCount = 0;

        foreach ($paths as $relPath) {
            $relPath = $this->normalizeRelPath(is_string($relPath) ? $relPath : '');
            $absPath = $this->mediaAbsPath($relPath);

            if ($absPath && File::exists($absPath)) {
                $res = $this->optimizeFile($absPath, $quality);
                if ($res['saved_bytes'] > 0 || $res['converted_webp']) {
                    $totalSavedBytes += $res['saved_bytes'];
                    $optimizedCount++;
                }
            }
        }

        $humanSaved = $this->formatBytes($totalSavedBytes);
        return back()->with('status', "⚡ Bulk WebP Conversion & Optimization Complete at {$quality}% Quality! Converted/optimized {$optimizedCount} image(s) to WebP format and saved {$humanSaved} disk storage.");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'relative_path' => ['required', 'string'],
        ]);

        $relPath = $this->normalizeRelPath($request->input('relative_path'));
        $absPath = $this->mediaAbsPath($relPath);
        if (!$absPath) {
            return back()->withErrors(['image' => 'Only images inside the media library can be deleted.']);
        }

        if (File::exists($absPath)) {
            File::delete($absPath);
        }

        ProductImage::where('path', '/' . $relPath)
            ->orWhere('path', $relPath)
            ->delete();

        if ($this->refKey(setting('logo')) === $relPath) {
            Setting::updateOrCreate(['key' => 'logo'], ['value' => '']);
            Setting::forgetCache();
        }

        if ($this->refKey(setting('favicon')) === $relPath) {
            Setting::updateOrCreate(['key' => 'favicon'], ['value' => '']);
            Setting::forgetCache();
        }

        return back()->with('status', 'Image deleted successfully from Media Library!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'paths' => ['required', 'array'],
            'paths.*' => ['string'],
        ]);

        $deleted = 0;
        foreach ($request->input('paths') as $relPath) {
            $relPath = $this->normalizeRelPath($relPath);
            $absPath = $this->mediaAbsPath($relPath);
            if (!$absPath) {
                continue;
            }

            if (File::exists($absPath)) {
                File::delete($absPath);
                $deleted++;
            }

            ProductImage::where('path', '/' . $relPath)
                ->orWhere('path', $relPath)
                ->delete();
        }

        return back()->with('status', "Bulk delete complete! Deleted {$deleted} images.");
    }

    private function optimizeFile(string $absPath, int $quality = 80): array
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return ['saved_bytes' => 0, 'percent_saved' => 0, 'initial_bytes' => 0, 'final_bytes' => 0, 'converted_webp' => false];
        }

        $initialBytes = filesize($absPath);

        $rawData = @file_get_contents($absPath);
        $gdImage = $rawData ? @imagecreatefromstring($rawData) : null;

        if (!$gdImage) {
            return ['saved_bytes' => 0, 'percent_saved' => 0, 'initial_bytes' => $initialBytes, 'final_bytes' => $initialBytes, 'converted_webp' => false];
        }

        $isConvertWebP = ($ext !== 'webp');
        $targetAbsPath = $isConvertWebP ? preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $absPath) : $absPath;
        $tempPath = $targetAbsPath . '.tmp';

        imagealphablending($gdImage, false);
        imagesavealpha($gdImage, true);
        imagewebp($gdImage, $tempPath, $quality);
        imagedestroy($gdImage);

        if (file_exists($tempPath)) {
            $finalBytes = filesize($tempPath);

            if ($finalBytes > 0 && ($isConvertWebP || $finalBytes < $initialBytes)) {
                rename($tempPath, $targetAbsPath);

                $oldRel = $this->normalizeRelPath($absPath);
                $newRel = $this->normalizeRelPath($targetAbsPath);

                if ($isConvertWebP && $oldRel !== $newRel) {
                    // Update database references to the new WebP file, in the form each column stores it
                    // ("/uploads/x.png" for uploads, "banners/x.png" for files on the public disk).
                    $oldRefs = $this->refForms($oldRel);
                    $newRef = $this->storedRef($newRel);
                    ProductImage::whereIn('path', $oldRefs)->update(['path' => $newRef]);
                    Category::whereIn('image', $oldRefs)->update(['image' => $newRef]);
                    Brand::whereIn('logo', $oldRefs)->update(['logo' => $newRef]);
                    Brand::whereIn('banner', $oldRefs)->update(['banner' => $newRef]);
                    Banner::whereIn('image', $oldRefs)->update(['image' => $newRef]);

                    foreach (['logo', 'favicon'] as $key) {
                        if ($this->refKey(setting($key)) === $oldRel) {
                            Setting::updateOrCreate(['key' => $key], ['value' => $newRef]);
                        }
                    }

                    Setting::forgetCache();

                    // Delete original PNG/JPG file
                    if (file_exists($absPath) && $absPath !== $targetAbsPath) {
                        @unlink($absPath);
                    }
                }

                $savedBytes = max(0, $initialBytes - $finalBytes);
                $percentSaved = $initialBytes > 0 ? round(($savedBytes / $initialBytes) * 100, 1) : 0;

                return [
                    'saved_bytes' => $savedBytes,
                    'percent_saved' => $percentSaved,
                    'initial_bytes' => $initialBytes,
                    'final_bytes' => $finalBytes,
                    'converted_webp' => $isConvertWebP,
                ];
            } else {
                @unlink($tempPath);
            }
        }

        return ['saved_bytes' => 0, 'percent_saved' => 0, 'initial_bytes' => $initialBytes, 'final_bytes' => $initialBytes, 'converted_webp' => false];
    }

    /**
     * Absolute path of a media file, or null when the path leaves public/uploads or isn't an image
     * (so "../.env" or "index.php" can never be optimized or deleted).
     */
    private function mediaAbsPath(string $relPath): ?string
    {
        $inStorage = in_array(dirname($relPath), array_map(fn ($f) => 'storage/' . $f, array_keys(self::STORAGE_FOLDERS)), true);
        if ($relPath === '' || str_contains($relPath, '..') || (!str_starts_with($relPath, 'uploads/') && !$inStorage)
            || !preg_match('/\.(png|jpe?g|webp|gif|svg|avif)$/i', $relPath)) {
            return null;
        }

        return public_path($relPath);
    }

    /**
     * Library path ("uploads/x.png" or "storage/banners/x.png") of an image reference saved in the database.
     * Public-disk uploads are saved without "storage/" (e.g. "banners/x.png").
     */
    private function refKey(?string $ref): string
    {
        $ref = ltrim(str_replace('\\', '/', (string) $ref), '/');
        if ($ref === '' || str_starts_with($ref, 'http://') || str_starts_with($ref, 'https://')) {
            return '';
        }

        return str_starts_with($ref, 'uploads/') || str_starts_with($ref, 'storage/') ? $ref : 'storage/' . $ref;
    }

    /** Every way a library path may be saved in the database. */
    private function refForms(string $relPath): array
    {
        $forms = [$relPath, '/' . $relPath];
        if (str_starts_with($relPath, 'storage/')) {
            $disk = substr($relPath, strlen('storage/'));
            array_push($forms, $disk, '/' . $disk);
        }

        return $forms;
    }

    /** How a library path is saved in the database. */
    private function storedRef(string $relPath): string
    {
        return str_starts_with($relPath, 'storage/') ? substr($relPath, strlen('storage/')) : '/' . $relPath;
    }

    private function normalizeRelPath(?string $path): string
    {
        $path = (string) $path;
        $publicDir = str_replace('\\', '/', public_path());
        $cleanPath = str_replace('\\', '/', $path);

        if (str_starts_with(strtolower($cleanPath), strtolower($publicDir))) {
            $cleanPath = substr($cleanPath, strlen($publicDir));
        }

        return ltrim($cleanPath, '/');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
