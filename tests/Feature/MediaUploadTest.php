<?php

namespace Tests\Feature;

use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_upload_service_exists()
    {
        // Test FileUploadService exists
        $this->assertTrue(class_exists(FileUploadService::class));
    }

    public function test_file_upload_service_has_validate_method()
    {
        // Test file validation method exists
        $reflection = new \ReflectionClass(FileUploadService::class);
        $this->assertTrue($reflection->hasMethod('validateFile'));
    }

    public function test_file_upload_service_has_store_method()
    {
        // Test file storage method exists
        $reflection = new \ReflectionClass(FileUploadService::class);
        $this->assertTrue($reflection->hasMethod('storeFile'));
    }

    public function test_file_upload_service_has_allowed_mime_types()
    {
        // Test allowed MIME types method exists
        $service = app(FileUploadService::class);
        $mimeTypes = $service->getAllowedMimeTypes();
        $this->assertIsArray($mimeTypes);
        $this->assertNotEmpty($mimeTypes);
    }
}
