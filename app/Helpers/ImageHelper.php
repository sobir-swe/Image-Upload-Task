<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    public static function fetchImageAndProcess(string $url, int $userWidth, int $userHeight, ?string $text = null, int $cropWidth = 0, int $cropHeight = 0): array
    {
        $downloadResult = self::downloadImage($url);
        if (isset($downloadResult['error'])) {
            return $downloadResult;
        }

        $imageContents = Storage::disk('public')->get($downloadResult['path']);
        list($originalWidth, $originalHeight) = getimagesizefromstring($imageContents);

        $validationResult = self::validateDimensions($userWidth, $userHeight, $originalWidth, $originalHeight);
        if ($validationResult !== true) {
            return ['error' => $validationResult];
        }

        $imageResource = self::resizeImage($imageContents, 200, 200);

        if ($cropWidth > 0 && $cropHeight > 0) {
            $imageResource = self::cropImage($imageResource, $cropWidth, $cropHeight);
        }

        if ($text) {
            $imageResource = self::addTextToImage($imageResource, $text);
        }

        $fileName = 'images/' . uniqid() . '.jpg';
        ob_start();
        imagejpeg($imageResource);
        $imageData = ob_get_clean();

        Storage::disk('public')->put($fileName, $imageData);

        imagedestroy($imageResource);

        return [
            'path' => $fileName,
            'image_url' => Storage::url($fileName),
        ];
    }

    private static function resizeImage(string $imageContents, int $userWidth, int $userHeight)
    {
        $imageResource = imagecreatefromstring($imageContents);
        list($originalWidth, $originalHeight) = getimagesizefromstring($imageContents);

        $resizedImage = imagecreatetruecolor($userWidth, $userHeight);
        imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, $userWidth, $userHeight, $originalWidth, $originalHeight);

        imagedestroy($imageResource);
        return $resizedImage;
    }

    private static function addTextToImage($imageResource, string $text)
    {
        $textColor = imagecolorallocate($imageResource, 255, 50, 50);
        imagestring($imageResource, 10, 10, 10, $text, $textColor);
        return $imageResource;
    }

    private static function cropImage($imageResource, int $cropWidth, int $cropHeight)
    {
        $croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);
        imagecopy($croppedImage, $imageResource, 0, 0, 0, 0, $cropWidth, $cropHeight);
        return $croppedImage;
    }

    public static function validateDimensions(int $userWidth, int $userHeight, int $originalWidth, int $originalHeight): true|string
    {
        if ($userWidth < $originalWidth || $userHeight < $originalHeight) {
            return 'The provided dimensions are smaller than the original image dimensions.';
        }
        return true;
    }

    public static function downloadImage(string $url): array
    {
        $response = Http::get($url);
        if ($response->failed()) {
            return ['error' => 'Error fetching image.'];
        }

        $imageContents = $response->body();
        $filename = uniqid() . '.jpg';

        Storage::disk('public')->put('images/' . $filename, $imageContents);

        return [
            'path' => 'images/' . $filename,
            'image_url' => Storage::url('images/' . $filename),
        ];
    }
}
