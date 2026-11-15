<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    /** Videos the product description editor (or the library's own upload) can save. */
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'ogg', 'ogv', 'mkv'];

    /**
     * Videos in the library, newest first: the ones uploaded from a product description and the
     * ones uploaded here. The product editor lists them so a video can be used again.
     */
    public static function videoFiles(): array
    {
        $videos = [];
        foreach (['uploads/products/description', 'uploads/media'] as $dir) {
            if (! File::isDirectory(public_path($dir))) {
                continue;
            }
            foreach (File::files(public_path($dir)) as $file) {
                if (! in_array(strtolower($file->getExtension()), self::VIDEO_EXTENSIONS, true)) {
                    continue;
                }
                $videos[] = [
                    'url' => asset($dir . '/' . $file->getFilename()),
                    'path' => $dir . '/' . $file->getFilename(),
                    'filename' => $file->getFilename(),
                    'size' => self::humanBytes($file->getSize()),
                    'modified' => $file->getMTime(),
                ];
            }
        }
        usort($videos, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        return $videos;
    }

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

        // Product descriptions that point at uploaded files (images or videos added in the editor)
        $descriptions = Product::query()
            ->where(fn ($q) => $q->where('description', 'like', '%uploads/%')->orWhere('short_description', 'like', '%uploads/%'))
            ->get(['id', 'name', 'description', 'short_description'])
            ->map(fn ($p) => ['name' => $p->name, 'html' => $p->description . ' ' . $p->short_description]);

        $logoPath = $this->refKey(setting('logo'));
        $faviconPath = $this->refKey(setting('favicon'));

        // 2. Scan public upload directories
        $directories = [
            public_path('uploads/products') => 'products',
            public_path('uploads/products/description') => 'products',
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
                $isVideo = in_array($extension, self::VIDEO_EXTENSIONS, true);
                if (!$isVideo && !in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                    continue;
                }

                $relativePath = $this->normalizeRelPath($pathname);
                $url = asset($relativePath);
                $sizeBytes = $file->getSize();
                $lastModified = $file->getMTime();

                // Determine usage reference
                $usedBy = null;
                $categoryType = $isVideo ? 'videos' : $defaultCategory;

                $altText = null;
                $colorTag = null;
                $dbImgId = null;

                if (isset($productImages[$relativePath])) {
                    $pImg = $productImages[$relativePath];
                    $usedBy = 'Product: ' . ($pImg->product?->name ?? 'Product #' . $pImg->product_id);
                    $categoryType = $isVideo ? 'videos' : 'products';
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
                } else {
                    $inDescription = $descriptions->first(fn ($d) => str_contains($d['html'], $relativePath));
                    if ($inDescription) {
                        $usedBy = 'Description: ' . $inDescription['name'];
                    }
                }

                // Check resolution dimensions if available
                $dimensions = null;
                if (!$isVideo && $extension !== 'svg' && @getimagesize($pathname)) {
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
                    'is_video' => $isVideo,
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
            'videoCount' => count(array_filter($allFiles, fn($f) => $f['is_video'])),
            'currentFilter' => $filterType,
            'currentSearch' => $search,
            'compressionQuality' => (int) setting('media_compression_quality', 80),
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'max:51200'],
        ]);

        $file = $request->file('image');
        // The extension comes from the file's real content, never the uploaded name: "photo.html" holding
        // a PNG is saved as .png, so an upload can't become a web page or script on the store's domain.
        $ext = \App\Support\SafeUpload::imageExtension($file);
        $isVideo = false;
        if ($ext === null) {
            $ext = \App\Support\SafeUpload::videoExtension($file);
            $isVideo = $ext !== null;
        }
        if ($ext === null) {
            return back()->withErrors(['image' => 'Please upload a JPG, PNG, WebP or GIF image, or an MP4, WebM or MOV video.']);
        }
        if (! $isVideo && $file->getSize() > 5 * 1024 * 1024) {
            return back()->withErrors(['image' => 'Images can be up to 5MB.']);
        }
        $fileName = time() . '_' . (Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: ($isVideo ? 'video' : 'image')) . '.' . $ext;
        $targetDir = public_path('uploads/media');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0777, true, true);
        }

        $file->move($targetDir, $fileName);

        return back()->with('status', ($isVideo ? 'Video' : 'Image') . ' uploaded successfully to Media Library!');
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
        if ($this->isVideoPath($relPath)) {
            return back()->withErrors(['image' => 'Videos are kept as they are; only images can be optimized.']);
        }

        $quality = max(10, min(100, (int) $request->input('quality', setting('media_compression_quality', 80))));
        $result = $this->optimizeFile($absPath, $quality);

        if (! empty($result['failed'])) {
            return back()->withErrors(['image' => "This image could not be converted to WebP, so it was left as it is ({$relPath})."]);
        }

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
        $skipped = 0;

        foreach ($paths as $relPath) {
            $relPath = $this->normalizeRelPath(is_string($relPath) ? $relPath : '');
            $absPath = $this->mediaAbsPath($relPath);

            if ($absPath && File::exists($absPath)) {
                $res = $this->optimizeFile($absPath, $quality);
                if (! empty($res['failed'])) {
                    $skipped++;
                } elseif ($res['saved_bytes'] > 0 || $res['converted_webp']) {
                    $totalSavedBytes += $res['saved_bytes'];
                    $optimizedCount++;
                }
            }
        }

        $humanSaved = $this->formatBytes($totalSavedBytes);
        $skippedNote = $skipped ? " {$skipped} image(s) could not be converted and were left as they are." : '';

        return back()->with('status', "⚡ Bulk WebP Conversion & Optimization Complete at {$quality}% Quality! Converted/optimized {$optimizedCount} image(s) to WebP format and saved {$humanSaved} disk storage.{$skippedNote}");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'relative_path' => ['required', 'string'],
        ]);

        $relPath = $this->normalizeRelPath($request->input('relative_path'));
        $absPath = $this->mediaAbsPath($relPath);
        if (!$absPath) {
            return back()->withErrors(['image' => 'Only files inside the media library can be deleted.']);
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

        return back()->with('status', ($this->isVideoPath($relPath) ? 'Video' : 'Image') . ' deleted from the Media Library.');
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

        return back()->with('status', "Bulk delete complete! Deleted {$deleted} file(s).");
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

        // WebP needs a full-colour image: palette (8-bit) PNGs and GIFs are converted first
        if (! imageistruecolor($gdImage)) {
            imagepalettetotruecolor($gdImage);
        }
        imagealphablending($gdImage, false);
        imagesavealpha($gdImage, true);
        try {
            $written = imagewebp($gdImage, $tempPath, $quality);
        } catch (\Throwable $e) {
            $written = false;
            report($e);
        }
        imagedestroy($gdImage);

        // A file that cannot be converted is left exactly as it was, so one odd image never stops a bulk run
        if (! $written) {
            @unlink($tempPath);

            return ['saved_bytes' => 0, 'percent_saved' => 0, 'initial_bytes' => $initialBytes, 'final_bytes' => $initialBytes, 'converted_webp' => false, 'failed' => true];
        }

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

                    // Images placed in a product description are linked by their URL inside the HTML
                    Product::query()
                        ->where(fn ($q) => $q->where('description', 'like', '%' . $oldRel . '%')->orWhere('short_description', 'like', '%' . $oldRel . '%'))
                        ->get(['id', 'description', 'short_description'])
                        ->each(fn ($p) => $p->forceFill([
                            'description' => $p->description === null ? null : str_replace($oldRel, $newRel, $p->description),
                            'short_description' => $p->short_description === null ? null : str_replace($oldRel, $newRel, $p->short_description),
                        ])->saveQuietly());

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
     * Absolute path of a media file, or null when the path leaves public/uploads or isn't an image or video
     * (so "../.env" or "index.php" can never be optimized or deleted).
     */
    private function mediaAbsPath(string $relPath): ?string
    {
        $inStorage = in_array(dirname($relPath), array_map(fn ($f) => 'storage/' . $f, array_keys(self::STORAGE_FOLDERS)), true);
        if ($relPath === '' || str_contains($relPath, '..') || (!str_starts_with($relPath, 'uploads/') && !$inStorage)
            || !preg_match('/\.(png|jpe?g|webp|gif|svg|avif|mp4|webm|mov|ogg|ogv|mkv)$/i', $relPath)) {
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

    private function isVideoPath(string $relPath): bool
    {
        return in_array(strtolower(pathinfo($relPath, PATHINFO_EXTENSION)), self::VIDEO_EXTENSIONS, true);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        return self::humanBytes($bytes, $precision);
    }

    private static function humanBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
