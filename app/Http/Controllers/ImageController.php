<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * On-demand image resizer for every image under /public.
 *
 *   /any/path/photo.jpg?size=300               -> 300x300, centered square crop (old behaviour)
 *   /any/path/photo.jpg?size=300&fit=contain   -> longest side = 300, aspect ratio kept (use for logos)
 *
 * Quality rules:
 *  - Imagick + Lanczos when the extension exists, otherwise GD with bicubic + gentle sharpen.
 *  - Upscaling is done with a real resampling filter (no browser-side SVG stretching).
 *  - Lossless PNG output for png/gif/bmp, JPEG q95 without chroma subsampling, WebP q95.
 *  - Results are cached on disk, so each size is generated only once.
 */
class ImageController extends Controller
{
    private const MAX_SIZE = 10000;
    private const MAX_SOURCE_BYTES = 20 * 1024 * 1024;
    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/x-ms-bmp',
    ];

    public function __invoke(Request $request, ?string $path = null)
    {
        $path = $path ?? $request->query('path');
        if (!is_string($path) || $path === '') {
            abort(404);
        }

        $fullPath = public_path($path);
        $realPath = realpath($fullPath);
        $publicPath = realpath(public_path());
        if (!$realPath || !$publicPath || !str_starts_with($realPath, $publicPath . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
            abort(404);
        }

        $sizeValue = $request->query('size');
        if ($sizeValue === null) {
            return response()->file($realPath, ['Cache-Control' => 'public, max-age=86400']);
        }
        if (!is_scalar($sizeValue) || !ctype_digit((string) $sizeValue) || (int) $sizeValue < 1 || (int) $sizeValue > self::MAX_SIZE) {
            abort(422, 'Image size must be between 1 and ' . self::MAX_SIZE . ' pixels.');
        }
        $size = (int) $sizeValue;
        $fit = $request->query('fit') === 'contain' ? 'contain' : 'cover';

        if (filesize($realPath) > self::MAX_SOURCE_BYTES) {
            abort(422, 'This image cannot be resized.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            abort(415, 'This image format cannot be resized.');
        }

        // jpeg/webp stay as they are; png, gif and bmp become lossless PNG (gif/bmp palettes look bad when scaled).
        $outFormat = match ($mime) {
            'image/jpeg' => 'jpeg',
            'image/webp' => 'webp',
            default => 'png',
        };
        $outMime = 'image/' . $outFormat;

        // ---- disk cache: one file per (source file version, size, fit) ----
        $cacheDir = storage_path('app/image-cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        $key = sha1($realPath . '|' . filemtime($realPath) . '|' . filesize($realPath) . '|' . $size . '|' . $fit . '|v2');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $key . '.' . $outFormat;

        if (!is_file($cacheFile)) {
            @set_time_limit(120);
            $contents = extension_loaded('imagick')
                ? $this->resizeWithImagick($realPath, $size, $fit, $outFormat)
                : $this->resizeWithGd($realPath, $size, $fit, $outFormat);

            $tmp = $cacheFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
            if (@file_put_contents($tmp, $contents) !== false) {
                @rename($tmp, $cacheFile);
            } else {
                // cache dir not writable: still answer the request
                return response($contents, 200, [
                    'Content-Type' => $outMime,
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        return response()->file($cacheFile, [
            'Content-Type' => $outMime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /** Work out source crop rectangle and destination size. */
    private function geometry(int $srcW, int $srcH, int $size, string $fit): array
    {
        if ($fit === 'contain') {
            $scale = $size / max($srcW, $srcH);
            return [0, 0, $srcW, $srcH, max(1, (int) round($srcW * $scale)), max(1, (int) round($srcH * $scale))];
        }
        $crop = min($srcW, $srcH);
        return [(int) floor(($srcW - $crop) / 2), (int) floor(($srcH - $crop) / 2), $crop, $crop, $size, $size];
    }

    private function resizeWithImagick(string $file, int $size, string $fit, string $outFormat): string
    {
        try {
            // [0] = first frame only (animated gif / multi-page)
            $img = new \Imagick($file . '[0]');
            if (method_exists($img, 'autoOrient')) {
                $img->autoOrient();
            }
            $img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

            [$sx, $sy, $sw, $sh, $dw, $dh] = $this->geometry($img->getImageWidth(), $img->getImageHeight(), $size, $fit);
            if ($sw !== $img->getImageWidth() || $sh !== $img->getImageHeight()) {
                $img->cropImage($sw, $sh, $sx, $sy);
                $img->setImagePage(0, 0, 0, 0);
            }

            $upscale = $dw > $sw;
            $img->resizeImage($dw, $dh, \Imagick::FILTER_LANCZOS, 1);
            // Lanczos is already sharp when shrinking; when enlarging add a light unsharp mask to fight softness.
            $img->unsharpMaskImage(0, $upscale ? 1.0 : 0.5, $upscale ? 0.7 : 0.4, 0.02);

            switch ($outFormat) {
                case 'jpeg':
                    $img->setImageBackgroundColor('white');
                    $img = $img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    $img->setImageFormat('jpeg');
                    $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $img->setImageCompressionQuality(95);
                    $img->setSamplingFactors(['1x1', '1x1', '1x1']); // no colour bleeding on sharp edges
                    $img->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
                    break;
                case 'webp':
                    $img->setImageFormat('webp');
                    $img->setImageCompressionQuality(95);
                    break;
                default:
                    $img->setImageFormat('png');
                    $img->setImageCompressionQuality(95);
            }

            $blob = $img->getImageBlob();
            $img->clear();

            return $blob;
        } catch (\ImagickException $e) {
            abort(422, 'This image cannot be resized.');
        }
    }

    private function resizeWithGd(string $file, int $size, string $fit, string $outFormat): string
    {
        if (!function_exists('imagecreatefromstring')) {
            abort(422, 'This image cannot be resized.');
        }

        $info = @getimagesize($file);
        if (!$info) {
            abort(422, 'This image cannot be resized.');
        }
        [$srcW, $srcH] = $info;
        [$sx, $sy, $sw, $sh, $dw, $dh] = $this->geometry($srcW, $srcH, $size, $fit);
        $upscale = $dw > $sw;

        // GD keeps every image as a raw 4-bytes-per-pixel bitmap, so a 10000x10000 photo needs ~380 MB
        // even if the file is only 4 MB. Work out what we need and raise PHP's limit; if the server
        // still cannot give it, stop with a clean error instead of a blank 500 page.
        $needed = ($srcW * $srcH * 4)                       // decoded source
            + ($upscale ? $sw * $sh * 4 : 0)                // cropped copy (only when enlarging)
            + ($dw * $dh * 4 * ($upscale ? 3 : 1))          // result (+ working copies when enlarging)
            + memory_get_usage() + 48 * 1024 * 1024;        // current use + safety margin
        if ($needed > 3 * 1024 ** 3) {
            abort(422, 'This image is too large to resize on this server.');
        }
        $limit = $this->memoryLimitBytes();
        if ($limit > 0 && $needed > $limit) {
            @ini_set('memory_limit', (string) $needed);
            $limit = $this->memoryLimitBytes();
            if ($limit > 0 && $needed > $limit) {
                abort(422, 'Image is too large for this server memory. Enable the Imagick PHP extension or raise memory_limit.');
            }
        }

        $source = @imagecreatefromstring(file_get_contents($file));
        if (!$source) {
            abort(422, 'This image cannot be resized.');
        }

        if ($upscale) {
            // Crop first, then enlarge in <=2x bicubic steps: much smoother than one big jump.
            $current = imagecreatetruecolor($sw, $sh);
            imagealphablending($current, false);
            imagesavealpha($current, true);
            imagefill($current, 0, 0, imagecolorallocatealpha($current, 0, 0, 0, 127));
            imagecopy($current, $source, 0, 0, $sx, $sy, $sw, $sh);
            imagedestroy($source);

            while (imagesx($current) * 2 < $dw) {
                $next = imagescale($current, imagesx($current) * 2, imagesy($current) * 2, IMG_BICUBIC);
                if (!$next) {
                    break;
                }
                imagedestroy($current);
                $current = $next;
            }
            $final = imagescale($current, $dw, $dh, IMG_BICUBIC);
            imagedestroy($current);
        } else {
            // Shrinking: resample straight from the source rectangle (no extra full-size copy), best GD quality.
            $final = imagecreatetruecolor($dw, $dh);
            imagealphablending($final, false);
            imagesavealpha($final, true);
            imagefill($final, 0, 0, imagecolorallocatealpha($final, 0, 0, 0, 127));
            imagecopyresampled($final, $source, 0, 0, $sx, $sy, $dw, $dh, $sw, $sh);
            imagedestroy($source);
        }
        if (!$final) {
            abort(422, 'This image cannot be resized.');
        }

        imagealphablending($final, false);
        imagesavealpha($final, true);
        // gentle sharpen (kernel sums to 1, so brightness is unchanged)
        imageconvolution($final, [[0, -0.25, 0], [-0.25, 2, -0.25], [0, -0.25, 0]], 1, 0);

        ob_start();
        $written = match ($outFormat) {
            'jpeg' => $this->gdJpeg($final),
            'webp' => function_exists('imagewebp') ? imagewebp($final, null, 95) : false,
            default => imagepng($final, null, 9),
        };
        $contents = ob_get_clean();
        imagedestroy($final);

        if (!$written || $contents === '' || $contents === false) {
            abort(422, 'This image format cannot be resized.');
        }

        return $contents;
    }

    private function gdJpeg(\GdImage $image): bool
    {
        // flatten transparency onto white, JPEG has no alpha
        $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagealphablending($flat, true);
        imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imageinterlace($flat, true);
        $ok = imagejpeg($flat, null, 95);
        imagedestroy($flat);

        return $ok;
    }

    private function memoryLimitBytes(): int
    {
        $v = trim((string) ini_get('memory_limit'));
        if ($v === '' || $v === '-1') {
            return 0;
        }
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }
}