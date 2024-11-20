<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     *
     * @param string $url
     * @param int $userWidth
     * @param int $userHeight
     * @param string|null $text
     * @param int $cropWidth
     * @param int $cropHeight
     * @return array
     */
    public static function fetchImageAndProcess(
        string $url,
        int $userWidth,
        int $userHeight,
        ?string $text = null,
        int $cropWidth = 0,
        int $cropHeight = 0
    ): array {
        $downloadResult = self::downloadImage($url);

        if (isset($downloadResult['error'])) {
            return $downloadResult;
        }

        $imageContents = Storage::disk('public')->get($downloadResult['path']);
        $imageSize = @getimagesizefromstring($imageContents);

        if (!$imageSize) {
            return ['error' => 'Invalid image data or format.'];
        }

        [$originalWidth, $originalHeight] = $imageSize;

        $validationResult = self::validateDimensions($userWidth, $userHeight, $originalWidth, $originalHeight);
        if ($validationResult !== true) {
            return ['error' => $validationResult];
        }

        $imageResource = self::resizeImage($imageContents, $userWidth, $userHeight);

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

    /**
     *
     * @param string $imageContents
     * @param int $userWidth
     * @param int $userHeight
     * @return resource
     */
    private static function resizeImage(string $imageContents, int $userWidth, int $userHeight)
    {
        $imageResource = imagecreatefromstring($imageContents);
        [$originalWidth, $originalHeight] = getimagesizefromstring($imageContents);

        $resizedImage = imagecreatetruecolor($userWidth, $userHeight);
        imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, $userWidth, $userHeight, $originalWidth, $originalHeight);

        imagedestroy($imageResource);
        return $resizedImage;
    }

    /**
     * Add text to the image.
     *
     * @param resource $imageResource
     * @param string $text
     * @return resource
     */
    private static function addTextToImage($imageResource, string $text)
    {
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);

        $fontSize = 5;

        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);

        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        $textColor = imagecolorallocate($imageResource, 255, 50, 50);

        imagestring($imageResource, $fontSize, $x, $y, $text, $textColor);

        return $imageResource;
    }



    /**
     *
     * @param resource $imageResource
     * @param int $cropWidth
     * @param int $cropHeight
     * @return resource
     */
    private static function cropImage($imageResource, int $cropWidth, int $cropHeight)
    {
        $croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);
        imagecopy($croppedImage, $imageResource, 0, 0, 0, 0, $cropWidth, $cropHeight);
        return $croppedImage;
    }


    public static function validateDimensions(?int $userWidth, ?int $userHeight, ?int $originalWidth, ?int $originalHeight): true|string
    {
        if (is_null($originalWidth) || is_null($originalHeight)) {
            return 'Original dimensions are missing or invalid.';
        }

        if ($userWidth > $originalWidth || $userHeight > $originalHeight) {
            return 'The provided dimensions are larger than the original image dimensions.';
        }

        return true;
    }

    /**
     *
     * @param string $url
     * @return array
     */
    public static function downloadImage(string $url): array
    {
        $response = Http::get($url);

        if ($response->failed() || !$response->body()) {
            return ['error' => 'Error fetching image or empty response.'];
        }

        $imageContents = $response->body();

        if (!@imagecreatefromstring($imageContents)) {
            return ['error' => 'The URL does not point to a valid image.'];
        }

        $filename = uniqid() . '.jpg';
        Storage::disk('public')->put('images/' . $filename, $imageContents);

        return [
            'path' => 'images/' . $filename,
            'image_url' => Storage::url('images/' . $filename),
        ];
    }
}

