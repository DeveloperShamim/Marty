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
    public function index(Request $request)
    {
        $filterType = $request->query('type', 'all');
        $search = strtolower(trim((string) $request->query('search', '')));

        // 1. Gather database usage references
        $productImages = ProductImage::with('product')->get()->keyBy(function ($img) {
            return $this->normalizeRelPath($img->path);
        });

        $categories = Category::all();
        $categoryImages = [];
        foreach ($categories as $c) {
            if ($c->image) {
                $categoryImages[$this->normalizeRelPath($c->image)] = $c->name;
            }
        }

        $brands = Brand::all();
        $brandImages = [];
        foreach ($brands as $b) {
            if ($b->logo) $brandImages[$this->normalizeRelPath($b->logo)] = "Brand Logo: " . $b->name;
            if ($b->banner) $brandImages[$this->normalizeRelPath($b->banner)] = "Brand Banner: " . $b->name;
        }

        $banners = Banner::all();
        $bannerImages = [];
        foreach ($banners as $bn) {
            if ($bn->image) $bannerImages[$this->normalizeRelPath($bn->image)] = "Hero Banner: " . ($bn->title ?: 'Banner #' . $bn->id);
        }

        $logoPath = $this->normalizeRelPath((string) setting('logo', ''));
        $faviconPath = $this->normalizeRelPath((string) setting('favicon', ''));

        // 2. Scan public upload directories
        $directories = [
            public_path('uploads/products') => 'products',
            public_path('uploads/media')    => 'media',
            public_path('uploads')          => 'branding',
            public_path('storage')          => 'storage',
        ];

        $allFiles = [];
        $scannedPaths = [];

        foreach ($directories as $dirPath => $defaultCategory) {
            if (!File::exists($dirPath)) {
                File::makeDirectory($dirPath, 0777, true, true);
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

                if (isset($productImages[$relativePath])) {
                    $pImg = $productImages[$relativePath];
                    $usedBy = 'Product: ' . ($pImg->product?->name ?? 'Product #' . $pImg->product_id);
                    $categoryType = 'products';
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

    public function optimizeSingle(Request $request)
    {
        $request->validate([
            'relative_path' => ['required', 'string'],
        ]);

        $relPath = $this->normalizeRelPath($request->input('relative_path'));
        $absPath = public_path($relPath);

        if (!File::exists($absPath)) {
            return back()->withErrors(['image' => "Image file not found on disk: {$relPath}"]);
        }

        $result = $this->optimizeFile($absPath);

        if ($result['converted_webp']) {
            $msg = "⚡ Optimization & WebP Conversion complete! Converted image to WebP format (" . $this->formatBytes($result['initial_bytes']) . " → " . $this->formatBytes($result['final_bytes']) . ").";
        } elseif ($result['saved_bytes'] > 0) {
            $msg = "⚡ Optimization complete! Reduced file size by {$result['percent_saved']}% (" . $this->formatBytes($result['initial_bytes']) . " → " . $this->formatBytes($result['final_bytes']) . ").";
        } else {
            $msg = "Image is already fully optimized as WebP! No further size reduction was needed.";
        }

        return back()->with('status', $msg);
    }

    public function bulkOptimize(Request $request)
    {
        $paths = $request->input('paths', []);

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
            $relPath = $this->normalizeRelPath($relPath);
            $absPath = public_path($relPath);

            if (File::exists($absPath)) {
                $res = $this->optimizeFile($absPath);
                if ($res['saved_bytes'] > 0 || $res['converted_webp']) {
                    $totalSavedBytes += $res['saved_bytes'];
                    $optimizedCount++;
                }
            }
        }

        $humanSaved = $this->formatBytes($totalSavedBytes);
        return back()->with('status', "⚡ Bulk WebP Conversion & Optimization Complete! Converted/optimized {$optimizedCount} image(s) to WebP format and saved {$humanSaved} disk storage.");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'relative_path' => ['required', 'string'],
        ]);

        $relPath = $this->normalizeRelPath($request->input('relative_path'));
        $absPath = public_path($relPath);

        if (File::exists($absPath)) {
            File::delete($absPath);
        }

        ProductImage::where('path', '/' . $relPath)
            ->orWhere('path', $relPath)
            ->delete();

        if ($this->normalizeRelPath(setting('logo')) === $relPath) {
            Setting::updateOrCreate(['key' => 'logo'], ['value' => '']);
            Setting::forgetCache();
        }

        if ($this->normalizeRelPath(setting('favicon')) === $relPath) {
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
            $absPath = public_path($relPath);

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

    private function optimizeFile(string $absPath): array
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
        imagewebp($gdImage, $tempPath, 82);
        imagedestroy($gdImage);

        if (file_exists($tempPath)) {
            $finalBytes = filesize($tempPath);

            if ($finalBytes > 0 && ($isConvertWebP || $finalBytes < $initialBytes)) {
                rename($tempPath, $targetAbsPath);

                $oldRel = $this->normalizeRelPath($absPath);
                $newRel = $this->normalizeRelPath($targetAbsPath);

                if ($isConvertWebP && $oldRel !== $newRel) {
                    // Update database references to new WebP file
                    ProductImage::where('path', '/' . $oldRel)->orWhere('path', $oldRel)->update(['path' => '/' . $newRel]);
                    Category::where('image', '/' . $oldRel)->orWhere('image', $oldRel)->update(['image' => '/' . $newRel]);
                    Brand::where('logo', '/' . $oldRel)->orWhere('logo', $oldRel)->update(['logo' => '/' . $newRel]);
                    Brand::where('banner', '/' . $oldRel)->orWhere('banner', $oldRel)->update(['banner' => '/' . $newRel]);
                    Banner::where('image', '/' . $oldRel)->orWhere('image', $oldRel)->update(['image' => '/' . $newRel]);

                    if ($this->normalizeRelPath(setting('logo')) === $oldRel) {
                        Setting::updateOrCreate(['key' => 'logo'], ['value' => $newRel]);
                    }
                    if ($this->normalizeRelPath(setting('favicon')) === $oldRel) {
                        Setting::updateOrCreate(['key' => 'favicon'], ['value' => $newRel]);
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

    private function normalizeRelPath(string $path): string
    {
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
