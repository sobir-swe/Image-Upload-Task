<?php

namespace Tests\Feature;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $image;

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->image = Image::query()->create([
            'url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTNOvUxITQyzsqBTQ7WRT8_53yrLCaLE6luQg&s',
            'width' => 200,
            'height' => 200,
            'text' => 'Sample Text',
            'path' => 'images/sample.jpg',
        ]);
    }

    /**
     *
     * @return void
     */
    public function test_index()
    {
        $response = $this->getJson('/api/images');

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

        $response->assertJsonFragment(['id' => $this->image->id]);
    }

    /**
     *
     * @return void
     */
    public function test_store()
    {
        $imageData = [
            'url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTNOvUxITQyzsqBTQ7WRT8_53yrLCaLE6luQg&s',
            'width' => 200,
            'height' => 200,
            'text' => 'Sample Text',
        ];

        $response = $this->postJson('/api/images', $imageData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Image uploaded and saved successfully!',
            ]);

        Storage::disk('public')->assertExists('images/' . basename($response->json('image_url')));

        $this->assertDatabaseHas('images', [
            'url' => $imageData['url'],
            'width' => $imageData['width'],
            'height' => $imageData['height'],
            'text' => $imageData['text'],
        ]);
    }

    /**
     *
     * @return void
     */
    public function test_show()
    {
        $response = $this->getJson("/api/images/{$this->image->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->image->id,
                'url' => $this->image->url,
                'width' => $this->image->width,
                'height' => $this->image->height,
                'text' => $this->image->text,
            ]);
    }

    /**
     *
     * @return void
     */
    public function test_destroy()
    {
        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Image deleted successfully!',
            ]);

        $this->assertDatabaseMissing('images', [
            'id' => $this->image->id,
        ]);
    }
}
