<?php

namespace App\Domain\Ai\Support;

use Illuminate\Http\UploadedFile;

/**
 * Prepares images for vision calls: downsizes to a 1568 px long edge (what
 * vision models use anyway — saves tokens and upload time) and base64-encodes.
 */
final class ImagePrep
{
    public const MAX_EDGE = 1568;

    /** @return array{media_type: string, data: string} */
    public static function fromUpload(UploadedFile $file): array
    {
        return self::fromBytes((string) file_get_contents($file->getRealPath()), (string) $file->getMimeType());
    }

    /** @return array{media_type: string, data: string} */
    public static function fromBytes(string $bytes, string $mime): array
    {
        $mime = in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true) ? $mime : 'image/jpeg';
        if (function_exists('imagecreatefromstring') && ($image = @imagecreatefromstring($bytes)) !== false) {
            $w = imagesx($image);
            $h = imagesy($image);
            if (max($w, $h) > self::MAX_EDGE) {
                $scale = self::MAX_EDGE / max($w, $h);
                $resized = imagescale($image, (int) round($w * $scale), (int) round($h * $scale));
                if ($resized !== false) {
                    ob_start();
                    imagejpeg($resized, null, 85);
                    $bytes = (string) ob_get_clean();
                    $mime = 'image/jpeg';
                }
            }
        }

        return ['media_type' => $mime, 'data' => base64_encode($bytes)];
    }
}
