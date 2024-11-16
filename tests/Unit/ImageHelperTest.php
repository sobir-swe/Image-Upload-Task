<?php

namespace Tests\Unit;

use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageHelperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

//    public function test_fetch_image_and_process()
//    {
//        $url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTNOvUxITQyzsqBTQ7WRT8_53yrLCaLE6luQg&s';
//        $userWidth = 200;
//        $userHeight = 200;
//        $text = 'Sample Text';
//        $cropWidth = 100;
//        $cropHeight = 100;
//
//        Storage::fake('public');
//        Http::fake([
//            $url => Http::response('sample image content'),
//        ]);
//
//        $result = ImageHelper::fetchImageAndProcess($url, $userWidth, $userHeight, $text, $cropWidth, $cropHeight);
//
//        $this->assertArrayHasKey('path', $result);
//        $this->assertArrayHasKey('image_url', $result);
//
//        Storage::disk('public')->assertExists($result['path']);
//
//        $this->assertStringContainsString('images/', $result['image_url']);
//    }

    public function test_download_image()
    {
        $url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTNOvUxITQyzsqBTQ7WRT8_53yrLCaLE6luQg&s';

        Http::fake([
            $url => Http::response('sample image content', 200),
        ]);

        $result = ImageHelper::downloadImage($url);

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('image_url', $result);

        Storage::disk('public')->assertExists($result['path']);
    }

    public function test_validate_dimensions()
    {
        $userWidth = 200;
        $userHeight = 200;
        $originalWidth = 400;
        $originalHeight = 400;

        $result = ImageHelper::validateDimensions($userWidth, $userHeight, $originalWidth, $originalHeight);
        $this->assertEquals('The provided dimensions are smaller than the original image dimensions.', $result);

        $userWidth = 500;
        $userHeight = 500;
        $result = ImageHelper::validateDimensions($userWidth, $userHeight, $originalWidth, $originalHeight);
        $this->assertTrue($result);
    }
}
