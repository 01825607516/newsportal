<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
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
        if (!is_scalar($sizeValue) || !ctype_digit((string) $sizeValue) || (int) $sizeValue < 1 || (int) $sizeValue > 10000) {
            abort(422, 'Image size must be between 1 and 10000 pixels.');
        }

        $size = (int) $sizeValue;
        if (filesize($realPath) > 20 * 1024 * 1024) {
            abort(422, 'This image cannot be resized.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/x-ms-bmp'], true)) {
            abort(415, 'This image format cannot be resized.');
        }

        // Keep large requested dimensions exact without allocating a huge GD bitmap.
        // The browser renders this SVG at the requested intrinsic size and fits it to the tab.
        if ($size > 2400) {
            $embeddedImage = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($realPath));
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '"><image href="' . $embeddedImage . '" width="' . $size . '" height="' . $size . '" preserveAspectRatio="xMidYMid slice"/></svg>';

            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml; charset=UTF-8',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (!function_exists('imagecreatefromstring')) {
            abort(422, 'This image cannot be resized.');
        }

        $source = @imagecreatefromstring(file_get_contents($realPath));
        if (!$source) {
            abort(422, 'This image cannot be resized.');
        }

        // Crop to a centered square, then resize to the requested size x size.
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropSize = min($sourceWidth, $sourceHeight);
        $sourceX = (int) floor(($sourceWidth - $cropSize) / 2);
        $sourceY = (int) floor(($sourceHeight - $cropSize) / 2);
        $target = imagecreatetruecolor($size, $size);

        if (in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefill($target, 0, 0, $transparent);
        }
        imagecopyresampled($target, $source, 0, 0, $sourceX, $sourceY, $size, $size, $cropSize, $cropSize);

        ob_start();
        $written = match ($mime) {
            'image/png' => imagepng($target, null, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($target, null, 85) : false,
            'image/gif' => imagegif($target),
            default => imagejpeg($target, null, 85),
        };
        $contents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        if (!$written) {
            abort(422, 'This image format cannot be resized.');
        }

        return response($contents, 200, [
            'Content-Type' => in_array($mime, ['image/bmp', 'image/x-ms-bmp'], true) ? 'image/jpeg' : $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
