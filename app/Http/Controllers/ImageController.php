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
            return response()->json(['error' => 'Rasmni olishda xatolik yuz berdi.'], 400);
        }

        list($originalWidth, $originalHeight) = getimagesizefromstring($imageContents);

        if ($userWidth > $originalWidth || $userHeight > $originalHeight) {
            return response()->json([
                'error' => 'Kiritilgan o‘lchamlar rasmning haqiqiy o‘lchamlaridan kattaroq. Iltimos, kichik width va height kiriting.',
                'required_width' => $originalWidth,
                'required_height' => $originalHeight
            ], 400);
        }

        $width = 200;
        $height = 200;

        $imageResource = imagecreatefromstring($imageContents);
        $resizedImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        if ($text) {
            $textColor = imagecolorallocate($resizedImage, 255, 255, 255);
            imagestring($resizedImage, 5, 50, 50, $text, $textColor);
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
            'message' => 'Rasm muvaffaqiyatli yuklab olindi va saqlandi!',
            'image_url' => Storage::url($fileName)
        ], 200);
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $images = Image::all()->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => Storage::url($image->path),
            ];
        });

        return response()->json(['images' => $images]);
    }
}
