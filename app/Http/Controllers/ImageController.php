<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $images = Image::all()->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => Storage::url($image->path),
                'width' => $image->width,
                'height' => $image->height,
                'text' => $image->text,
            ];
        });

        return response()->json(['images' => $images]);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        $text = $request->input('text');

        $result = ImageHelper::fetchImageAndProcess($url, $userWidth, $userHeight, $text);

        if (isset($result['error'])) {
            return response()->json($result, 400);
        }

        $image = Image::query()->create([
            'url' => $url,
            'width' => $userWidth,
            'height' => $userHeight,
            'text' => $text,
            'path' => $result['path'],
        ]);

        return response()->json([
            'message' => 'Image uploaded and saved successfully!',
            'image_url' => $result['image_url'],
        ], 201);
    }

    /**
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $image = Image::query()->findOrFail($id);
        return response()->json($image);
    }

    /**
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $image = Image::query()->findOrFail($id);
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully!',
        ]);
    }
}
