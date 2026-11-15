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

    /**
     * The biggest upload this server accepts, in bytes: the smaller of PHP's upload_max_filesize and
     * post_max_size (0 when neither is set). Hosts often keep these at 8MB, which is too small for video.
     */
    public static function serverLimitBytes(): int
    {
        $limits = array_filter([self::iniBytes(ini_get('upload_max_filesize')), self::iniBytes(ini_get('post_max_size'))]);

        return $limits ? min($limits) : 0;
    }

    /** "8M" style php.ini size as a readable "8 MB". */
    public static function humanLimit(): string
    {
        $bytes = self::serverLimitBytes();

        return $bytes ? round($bytes / 1048576, 1) . ' MB' : 'no limit';
    }

    private static function iniBytes($value): int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }
        $num = (float) $value;

        return (int) match (strtolower(substr($value, -1))) {
            'g' => $num * 1073741824,
            'm' => $num * 1048576,
            'k' => $num * 1024,
            default => $num,
        };
    }

    /** mp4 / webm / mov for a real video file, null for anything else. */
    public static function videoExtension(UploadedFile $file): ?string
    {
        $ext = strtolower((string) $file->extension()); // guessed from the content, not the name
        $ext = match ($ext) { 'm4v', 'mp4s' => 'mp4', 'qt' => 'mov', default => $ext };

        return in_array($ext, ['mp4', 'webm', 'mov'], true) ? $ext : null;
    }
}
