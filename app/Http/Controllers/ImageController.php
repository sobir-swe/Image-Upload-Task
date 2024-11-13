<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;

class ImageController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'text' => 'nullable|string',
        ]);

        $url = $request->input('url');
        $userWidth = $request->input('width');
        $userHeight = $request->input('height');
        $text = $request->input('text', null);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $imageContents = curl_exec($ch);
        curl_close($ch);

        if ($imageContents === false) {
            return response()->json(['error' => 'Error fetching image.'], 400);
        }

        list($originalWidth, $originalHeight) = getimagesizefromstring($imageContents);

        if ($userWidth > $originalWidth || $userHeight > $originalHeight) {
            return response()->json([
                'error' => 'The entered dimensions exceed the original image size. Please enter smaller width and height.',
                'required_width' => $originalWidth,
                'required_height' => $originalHeight
            ], 400);
        }

        $imageResource = imagecreatefromstring($imageContents);
        $resizedImage = imagecreatetruecolor(200, 200);

        imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, 200, 200, $originalWidth, $originalHeight);

        if ($text) {
            $textColor = imagecolorallocate($resizedImage, 255, 255, 255);
            imagestring($resizedImage, 5, 10, 10, $text, $textColor);
        }

        $fileName = 'images/' . uniqid() . '.jpg';

        ob_start();
        imagejpeg($resizedImage);
        $imageData = ob_get_clean();

        Storage::disk('public')->put($fileName, $imageData);

        Image::query()->create([
            'url' => $url,
            'width' => $userWidth,
            'height' => $userHeight,
            'text' => $text,
            'path' => $fileName,
        ]);

        imagedestroy($imageResource);
        imagedestroy($resizedImage);

        return response()->json([
            'message' => 'Image uploaded and saved successfully!',
            'image_url' => Storage::url($fileName)
        ], 201);
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $images = Image::all()->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => Storage::url($image->path),
                'width' => $image->width,
                'height' => $image->height,
            ];
        });

        return response()->json(['images' => $images]);
    }
}
