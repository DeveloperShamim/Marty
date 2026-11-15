<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * File extensions for saved uploads, taken from the file's detected content type.
 * The uploaded name is chosen by whoever uploads it, so "x.html" or "x.php" holding image bytes
 * would otherwise be saved and served as a web page or script.
 */
class SafeUpload
{
    private const IMAGE_EXTENSIONS = ['jpg', 'png', 'webp', 'gif', 'avif'];

    /** jpg / png / webp / gif / avif for a real raster image, null for anything else (SVG included). */
    public static function imageExtension(UploadedFile $file): ?string
    {
        $ext = strtolower((string) $file->extension()); // guessed from the content, not the name
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;

        return in_array($ext, self::IMAGE_EXTENSIONS, true) ? $ext : null;
    }

    /** mp4 / webm / mov for a real video file, null for anything else. */
    public static function videoExtension(UploadedFile $file): ?string
    {
        $ext = strtolower((string) $file->extension()); // guessed from the content, not the name
        $ext = match ($ext) { 'm4v' => 'mp4', 'qt' => 'mov', default => $ext };

        return in_array($ext, ['mp4', 'webm', 'mov'], true) ? $ext : null;
    }
}
