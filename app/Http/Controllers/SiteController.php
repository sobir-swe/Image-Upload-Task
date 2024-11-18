<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{

    public function index(): \Illuminate\Http\JsonResponse
    {
        $sites = Site::all();
        return response()->json($sites);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'text' => 'required|string',
        ]);

        $site = Site::create([
            'url' => $validated['url'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'text' => $validated['text'],
        ]);

        $html = file_get_contents($validated['url']);
        $dom = new HTMLDocument();
        $dom->load($html);

        $imageUrls = [];
        foreach ($dom->find('img') as $img) {
            if ($img->src) {
                $imageUrls[] = $img->src;
            }
        }

        $largeImagesCount = 0;
        foreach ($imageUrls as $imageUrl) {
            $imageSize = @getimagesize($imageUrl);
            if ($imageSize && $imageSize[0] > $validated['width'] && $imageSize[1] > $validated['height']) {
                $largeImagesCount++;
            }
        }

        return response()->json([
            'site' => $site,
            'largeImagesCount' => $largeImagesCount
        ]);
    }




    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(Site::query()->findOrFail($id));
    }


    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $site = Site::query()->findOrFail($id);
        $site->delete();
        return response()->json([
            'message' => 'Site deleted successfully'
        ]);
    }
}
