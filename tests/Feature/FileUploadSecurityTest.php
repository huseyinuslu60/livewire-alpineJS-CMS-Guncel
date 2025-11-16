<?php

namespace Tests\Feature;

use App\Models\User;
use App\Traits\SecureFileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase, SecureFileUpload;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_php_files_are_rejected()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake PHP file
        $file = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $errors = $this->validateFile($file);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('güvenlik', implode(' ', $errors));
    }

    public function test_html_files_are_rejected()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake HTML file
        $file = UploadedFile::fake()->create('malicious.html', 100, 'text/html');

        $errors = $this->validateFile($file);
        $this->assertNotEmpty($errors);
    }

    public function test_executable_files_are_rejected()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake executable file
        $file = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $errors = $this->validateFile($file);
        $this->assertNotEmpty($errors);
    }

    public function test_valid_image_files_are_accepted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a valid JPEG image
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $errors = $this->validateFile($file);
        $this->assertEmpty($errors);
    }

    public function test_valid_pdf_files_are_accepted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a valid PDF file with actual PDF content
        // UploadedFile::fake()->create() may not set MIME type correctly, so use createWithContent
        $pdfContent = '%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>
endobj
xref
0 4
trailer
<< /Size 4 /Root 1 0 R >>
startxref
123
%%EOF';
        $file = UploadedFile::fake()->createWithContent('document.pdf', $pdfContent);

        $errors = $this->validateFile($file);
        $this->assertEmpty($errors, 'PDF dosyası kabul edilmeli. Hatalar: '.implode(', ', $errors));
    }

    public function test_files_with_malicious_content_are_rejected()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a file with PHP code disguised as image
        $file = UploadedFile::fake()->createWithContent('image.jpg', '<?php system($_GET["cmd"]); ?>');

        $errors = $this->validateFile($file);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('güvenlik', implode(' ', $errors));
    }

    public function test_large_files_are_rejected()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Override max file size for this test
        $this->maxFileSize = 100; // 100KB

        // Create a file larger than limit by creating actual large content
        // UploadedFile::fake() may not respect size, so we create actual large file
        $largeContent = str_repeat('x', 150 * 1024); // 150KB - larger than 100KB limit
        $file = UploadedFile::fake()->createWithContent('large.jpg', $largeContent);

        $errors = $this->validateFile($file);
        // validateFile() returns array - verify large file is rejected
        $this->assertNotEmpty($errors, 'Large file should be rejected');
        $this->assertStringContainsString('boyutu', implode(' ', $errors));
    }

    public function test_file_upload_uses_uuid_filename()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('original-name.jpg');
        $path = $this->storeFileSecurely($file, 'test');

        // Path should contain UUID, not original filename
        $this->assertStringNotContainsString('original-name', $path);
        $this->assertMatchesRegularExpression('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.jpg/', $path);
    }
}
