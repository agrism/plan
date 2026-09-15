<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Process an uploaded image: resize, convert to WebP format, and upload to S3.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param int $maxWidth
     * @param int $quality
     * @return string Public URL of the uploaded WebP image on S3
     */
    public function processAndUpload(
        UploadedFile $file,
        string $folder = 'tasks',
        int $maxWidth = 1200,
        int $quality = 82
    ): string {
        $realPath = $file->getRealPath();
        
        // Read image info
        $imageInfo = @getimagesize($realPath);
        if (!$imageInfo) {
            // Fallback: if not standard image info, upload directly as fallback
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "{$folder}/{$filename}";
            Storage::disk('s3')->put($path, file_get_contents($realPath), 'public');
            return Storage::disk('s3')->url($path);
        }

        [$width, $height, $imageType] = $imageInfo;

        // Load image resource
        $sourceImage = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($realPath),
            IMAGETYPE_PNG => @imagecreatefrompng($realPath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($realPath),
            IMAGETYPE_GIF => @imagecreatefromgif($realPath),
            default => null,
        };

        if (!$sourceImage) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "{$folder}/{$filename}";
            Storage::disk('s3')->put($path, file_get_contents($realPath), 'public');
            return Storage::disk('s3')->url($path);
        }

        // Calculate proportional dimensions
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($height / $width) * $maxWidth);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create target truecolor canvas
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG / WebP / GIF
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
        imagealphablending($targetImage, true);

        // Resample image with high quality
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        // Output to buffer as WebP
        ob_start();
        imagewebp($targetImage, null, $quality);
        $webpData = ob_get_clean();

        // Free GD memory
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        // Store to Hetzner S3
        $filename = Str::uuid() . '.webp';
        $path = "{$folder}/{$filename}";

        Storage::disk('s3')->put($path, $webpData, [
            'visibility' => 'public',
            'mimetype' => 'image/webp',
            'CacheControl' => 'max-age=31536000, public',
        ]);

        return Storage::disk('s3')->url($path);
    }
}
