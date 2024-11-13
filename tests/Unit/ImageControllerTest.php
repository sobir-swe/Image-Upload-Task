<?php

namespace Tests\Unit;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_image_with_valid_data()
    {
        $imageUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSuIwDBbHmIWaEpkVpBTnOeUhisIR3dl1Urg&s';

        $response = $this->postJson('/images', [
            'url' => $imageUrl,
            'width' => 100,
            'height' => 100,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'image_url',
        ]);
    }


    public function test_store_image_with_large_dimensions()
    {
        $imageUrl = 'https://example.com/test.jpg';

        $response = $this->postJson('/images', [
            'url' => $imageUrl,
            'width' => 5000,
            'height' => 5000,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The entered dimensions exceed the original image size. Please enter smaller width and height.',
        ]);
    }


    public function test_store_image_with_invalid_url()
    {
        $invalidUrl = 'invalid-url';

        $response = $this->postJson('/images', [
            'url' => $invalidUrl,
            'width' => 100,
            'height' => 100,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('url');
    }


    public function test_index_returns_list_of_images()
    {
        $image = Image::query()->create([
            'url' => 'https://example.com/test.jpg',
            'width' => 100,
            'height' => 100,
            'text' => null,
            'path' => 'images/test.jpg',
        ]);

        $response = $this->getJson('/images');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'images' => [
                '*' => ['url', 'width', 'height'],
            ]
        ]);
    }

}
