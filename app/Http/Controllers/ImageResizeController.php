<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ImageResizeController extends Controller
{
    private const MAX_WIDTH = 4096;
    private const MAX_PIXELS = 20000000;
    private const MAX_SOURCE_BYTES = 20971520;

    public function show(Request $request, string $path): BinaryFileResponse|Response
    {
        $source = $this->resolveImagePath($path);
        $size = $request->query('size');

        if ($size === null) {
            return response()->file($source, $this->headers());
        }

        if (!is_scalar($size) || !preg_match('/^[1-9][0-9]{0,3}$/', (string) $size) || (int) $size > self::MAX_WIDTH) {
            abort(400, 'The size parameter must be a width between 1 and ' . self::MAX_WIDTH . ' pixels.');
        }

        if (!extension_loaded('gd')) {
            abort(503, 'Image resizing requires the PHP GD extension to be enabled.');
        }

        $imageInfo = getimagesize($source);
        if ($imageInfo === false) {
            abort(415, 'The requested file is not a supported image.');
        }

        [$width, $height] = $imageInfo;
        if ($width < 1 || $height < 1 || $width * $height > self::MAX_PIXELS || filesize($source) > self::MAX_SOURCE_BYTES) {
            abort(413, 'The source image is too large to resize.');
        }

        $mime = $imageInfo['mime'] ?? '';
        [$extension, $loader] = match ($mime) {
            'image/jpeg' => ['jpg', 'imagecreatefromjpeg'],
            'image/png' => ['png', 'imagecreatefrompng'],
            'image/gif' => ['gif', 'imagecreatefromgif'],
            'image/webp' => ['webp', 'imagecreatefromwebp'],
            'image/bmp', 'image/x-ms-bmp' => ['bmp', 'imagecreatefrombmp'],
            default => abort(415, 'This image format cannot be resized.'),
        };

        if ($width <= (int) $size) {
            return response()->file($source, $this->headers($mime));
        }

        $encoder = match ($mime) {
            'image/jpeg' => 'imagejpeg',
            'image/png' => 'imagepng',
            'image/gif' => 'imagegif',
            'image/webp' => 'imagewebp',
            'image/bmp', 'image/x-ms-bmp' => 'imagebmp',
        };

        if (!function_exists($loader) || !function_exists($encoder)) {
            abort(415, 'This image format is not supported by the installed PHP GD extension.');
        }

        $cacheDirectory = storage_path('app/image-cache');
        File::ensureDirectoryExists($cacheDirectory);
        $cachePath = $cacheDirectory . DIRECTORY_SEPARATOR . hash('sha256', 'v2|' . $source . '|' . filemtime($source) . '|' . filesize($source) . '|' . $size) . '.' . $extension;

        if (!is_file($cachePath)) {
            $sourceImage = $loader($source);
            if ($sourceImage === false) {
                abort(415, 'The requested image could not be decoded.');
            }

            $targetWidth = (int) $size;
            $targetHeight = max(1, (int) round($height * $targetWidth / $width));
            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($targetImage === false) {
                imagedestroy($sourceImage);
                abort(500, 'The resized image could not be created.');
            }

            if ($mime !== 'image/jpeg') {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
                imagefill($targetImage, 0, 0, $transparent);
            }

            $copied = imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            imagedestroy($sourceImage);
            if (!$copied) {
                imagedestroy($targetImage);
                abort(500, 'The image could not be resized.');
            }

            if ($mime === 'image/jpeg' && $width >= $targetWidth * 2) {
                $sharpened = imageconvolution($targetImage, [
                    [0, -0.5, 0],
                    [-0.5, 3, -0.5],
                    [0, -0.5, 0],
                ], 1, 0);
                if (!$sharpened) {
                    imagedestroy($targetImage);
                    abort(500, 'The resized image could not be sharpened.');
                }
            }

            $saved = match ($mime) {
                'image/jpeg' => imagejpeg($targetImage, $cachePath, 95),
                'image/png' => imagepng($targetImage, $cachePath, 6),
                'image/gif' => imagegif($targetImage, $cachePath),
                'image/webp' => imagewebp($targetImage, $cachePath, 95),
                'image/bmp', 'image/x-ms-bmp' => imagebmp($targetImage, $cachePath),
                default => false,
            };
            imagedestroy($targetImage);

            if (!$saved) {
                abort(500, 'The resized image could not be saved.');
            }
        }

        return response()->file($cachePath, $this->headers($mime));
    }

    private function resolveImagePath(string $path): string
    {
        $publicRoot = realpath(public_path());
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if ($publicRoot === false || $relativePath === '' || str_contains($relativePath, "\0")) {
            abort(404);
        }

        $source = realpath($publicRoot . DIRECTORY_SEPARATOR . $relativePath);
        $publicPrefix = strtolower(rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        if ($source === false || !is_file($source) || !str_starts_with(strtolower($source), $publicPrefix)) {
            abort(404);
        }

        return $source;
    }

    private function headers(?string $mime = null): array
    {
        $headers = [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($mime !== null) {
            $headers['Content-Type'] = $mime;
        }

        return $headers;
    }
}
