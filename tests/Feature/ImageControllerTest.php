<?php

namespace Tests\Feature;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if the index method returns the list of images.
     *
     * @return void
     */
    public function test_index()
    {
        // Create some images
        $image = Image::factory()->create();

        // Make a GET request to the index method
        $response = $this->getJson('/api/images');

        // Assert the response status and structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'images' => [
                    '*' => [
                        'id',
                        'url',
                        'width',
                        'height',
                        'text',
                    ]
                ]
            ]);

        // Check if the created image exists in the response
        $response->assertJsonFragment(['id' => $image->id]);
    }

    /**
     * Test the store method to upload and store an image.
     *
     * @return void
     */
    public function test_store()
    {
        // Mock storage
        Storage::fake('public');

        // Prepare the image data
        $imageData = [
            'url' => 'https://example.com/sample.jpg',
            'width' => 200,
            'height' => 200,
            'text' => 'Sample Text',
        ];

        // Make a POST request to the store method
        $response = $this->postJson('/api/images', $imageData);

        // Assert the response status and message
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Image uploaded and saved successfully!',
            ]);

        // Assert the image is stored in the storage
        Storage::disk('public')->assertExists('images/' . basename($response->json('image_url')));

        // Check if the image is saved in the database
        $this->assertDatabaseHas('images', [
            'url' => $imageData['url'],
            'width' => $imageData['width'],
            'height' => $imageData['height'],
            'text' => $imageData['text'],
        ]);
    }

    /**
     * Test the show method to retrieve a single image by ID.
     *
     * @return void
     */
    public function test_show()
    {
        // Create an image
        $image = Image::factory()->create();

        // Make a GET request to the show method
        $response = $this->getJson("/api/images/{$image->id}");

        // Assert the response status and check the image data
        $response->assertStatus(200)
            ->assertJson([
                'id' => $image->id,
                'url' => $image->url,
                'width' => $image->width,
                'height' => $image->height,
                'text' => $image->text,
            ]);
    }

    /**
     * Test the destroy method to delete an image.
     *
     * @return void
     */
    public function test_destroy()
    {
        // Create an image
        $image = Image::factory()->create();

        // Make a DELETE request to the destroy method
        $response = $this->deleteJson("/api/images/{$image->id}");

        // Assert the response status and message
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Image deleted successfully!',
            ]);

        // Assert the image is deleted from the database
        $this->assertDatabaseMissing('images', [
            'id' => $image->id,
        ]);
    }
}
