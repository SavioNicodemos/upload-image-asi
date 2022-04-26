<?php

namespace Tests\Feature;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    protected $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Image;
        Storage::fake(env('FILESYSTEM_DISK'));
    }

    public function testItShouldCreateANewImage()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/upload', [
            'image' => $file
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                    'id',
                    'name',
                    'path',
                    'created_at',
                    'updated_at'
                ])
            ->assertJsonFragment(['name' => $file->hashName()])
            ->assertJsonFragment(['path' => 'images/'.$file->hashName()]);
    }

    public function testItShouldHaveAnImageOnFolderWhenCreateANewImage()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post('/api/upload', [
            'image' => $file,
        ]);

        $this->assertTrue(Storage::allFiles()[0] == 'images/'.$file->hashName());

        $response->assertStatus(201);
    }

    public function testItShouldNotCreateAnImageWithEmptyBody()
    {
        $response = $this->postJson('/api/upload', []);

        $response->assertStatus(400);
    }

    public function testItShouldReturnAListOfImages()
    {
        $this->model->factory(10)->create();

        $response = $this->getJson('/api/upload');

        $response->assertOk()
            ->assertJsonCount(10)
            ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'path',
                'created_at',
                'updated_at'
            ]]);
    }

    public function testItShouldFindAnImage()
    {
        $this->model->factory()->create();

        $response = $this->getJson('/api/upload/1');

        $response->assertJsonStructure([
            'id',
            'name',
            'path',
            'created_at',
            'updated_at'
        ]);
    }

    public function testItShouldNotFindANonExistentImage()
    {
        $response = $this->getJson('/api/upload/1');

        $response->assertStatus(400);
    }

    public function testItShouldNotUpdateANonExistentImage()
    {
        $response = $this->putJson('/api/upload/1');

        $response->assertStatus(400);
    }

    public function testItShouldNotUpdateAnImageWithEmptyBody()
    {
        $this->model->factory()->create();

        $response = $this->putJson('/api/upload/1', []);

        $response->assertStatus(400);
    }

    public function testItShouldUpdateAnImage()
    {
        $this->model->factory()->create();

        $response = $this->putJson('/api/upload/1', [
            'image' => UploadedFile::fake()->image('avatar.jpg')
        ]);

        $response->assertOk();
    }

    public function testItShouldReplaceTheImageWhenItIsUpdated()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('avatar.jpg');

        $this->post('/api/upload', [
            'image' => $file,
        ]);

        $this->put('/api/upload/1', [
            'image' => $file2,
        ]);

        $this->assertTrue(count(Storage::allFiles()) == 1);
        $this->assertTrue(Storage::allFiles()[0] == 'images/'.$file2->hashName());
    }

    public function testItShouldNotDeleteANonExistentImage()
    {
        $response = $this->deleteJson('/api/upload/1');

        $response->assertStatus(400);
    }

    public function testItShouldDeleteAnImage()
    {
        $this->model->factory()->create();

        $response = $this->deleteJson('/api/upload/1');

        $response->assertOk();
    }

    public function testItShouldNotHaveAnImageOnFolderWhenIsDeleted()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->post('/api/upload', [
            'image' => $file,
        ]);

        $this->deleteJson('/api/upload/1');

        $this->assertTrue(Storage::allFiles() == []);
    }

}
