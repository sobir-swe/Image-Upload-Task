<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Image;
use App\Models\Site;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Http;

class SiteController extends Controller
{
    protected $imageController;

    public function __construct(ImageController $imageController)
    {
        $this->imageController = $imageController;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $sites = Site::all();
        return response()->json([
            'message' => 'Sites fetched successfully!',
            'data' => $sites
        ]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'text' => 'nullable|string',
            'largeImagesCount' => 'nullable|integer|min:0',
        ]);

        try {
            $response = Http::get($request->input('url'));
            if (!$response->successful()) {
                return response()->json(['message' => 'Unable to fetch content from the URL.'], 400);
            }
            $content = $response->body();

            $imageUrls = $this->getImageUrlsFromContent($content);

            if (empty($imageUrls)) {
                return response()->json(['message' => 'No images found on the provided URL.'], 404);
            }

            $site = Site::create([
                'url' => $request->input('url'),
                'width' => $request->input('width'),
                'height' => $request->input('height'),
                'text' => $request->input('text'),
            ]);

            $processedImages = [];
            foreach ($imageUrls as $imageUrl) {
                $imageUrl = $this->makeAbsoluteUrl($imageUrl, $request->input('url'));

                $result = ImageHelper::fetchImageAndProcess($imageUrl, $request->input('width'), $request->input('height'), $request->input('text'));

                if (!isset($result['error']) && isset($result['image_url'])) {
                    $processedImages[] = $result['image_url'];

                    Image::query()->create([
                        'url' => $imageUrl,
                        'width' => $request->input('width'),
                        'height' => $request->input('height'),
                        'text' => $request->input('text'),
                    ]);
                }
            }

            dump($processedImages);

            if ($request->has('largeImagesCount') && count($processedImages) !== (int)$request->input('largeImagesCount')) {
                return response()->json([
                    'message' => 'Mismatch in large images count!',
                    'foundImagesCount' => count($processedImages),
                ], 400);
            }

            return response()->json([
                'message' => 'Images processed successfully!',
                'images' => $processedImages,
                'largeImagesCount' => count($processedImages),
            ], 201);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTrace()], 500);
        }
    }

    protected function makeAbsoluteUrl($imageUrl, $baseUrl): string
    {
        if (filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
            return rtrim($baseUrl, '/') . '/' . ltrim($imageUrl, '/');
        }
        return $imageUrl;
    }

    protected function getImageUrlsFromContent($content): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches);
        return $matches[1] ?? [];
    }
}
