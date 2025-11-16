<?php

namespace Tests\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AgencyNews\Models\AgencyNews;
use Tests\TestCase;

class AgencyNewsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_news_can_be_created()
    {
        $agencyNewsData = [
            'title' => 'Test Agency News Title',
            'summary' => 'Test Agency News Summary',
            'tags' => 'test,news,agency',
            'original_id' => 12345,
            'agency_id' => 1,
            'category' => 'politics',
            'has_image' => true,
            'file_path' => 'test-image.jpg',
            'sites' => [1, 2, 3],
            'content' => 'Test Agency News Content',
        ];

        $agencyNews = AgencyNews::create($agencyNewsData);

        $this->assertInstanceOf(AgencyNews::class, $agencyNews);
        $this->assertEquals('Test Agency News Title', $agencyNews->title);
        $this->assertEquals(1, $agencyNews->agency_id);
        $this->assertTrue($agencyNews->has_image);
        $this->assertDatabaseHas('agency_news', ['title' => 'Test Agency News Title']);
    }

    public function test_agency_news_can_be_updated()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'Original Title',
            'summary' => 'Original Summary',
            'agency_id' => 1,
            'has_image' => false,
        ]);

        $agencyNews->update([
            'title' => 'Updated Title',
            'has_image' => true,
        ]);

        $this->assertEquals('Updated Title', $agencyNews->fresh()->title);
        $this->assertTrue($agencyNews->fresh()->has_image);
    }

    public function test_agency_news_can_be_deleted()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'News to Delete',
            'summary' => 'Summary to delete',
            'agency_id' => 1,
        ]);
        $recordId = $agencyNews->record_id;

        $agencyNews->delete();

        $this->assertSoftDeleted('agency_news', ['record_id' => $recordId]);
    }

    public function test_agency_news_has_required_attributes()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'Required Attributes News',
            'agency_id' => 1,
        ]);

        $this->assertNotNull($agencyNews->record_id);
        $this->assertNotNull($agencyNews->title);
        $this->assertNotNull($agencyNews->agency_id);
        $this->assertNotNull($agencyNews->created_at);
    }

    public function test_agency_news_can_have_image()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'News with Image',
            'agency_id' => 1,
            'has_image' => true,
            'file_path' => 'test-image.jpg',
        ]);

        $this->assertTrue($agencyNews->has_image);
        $this->assertEquals('test-image.jpg', $agencyNews->file_path);
    }

    public function test_agency_news_can_have_tags()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'News with Tags',
            'agency_id' => 1,
            'tags' => 'politics,economy,breaking',
        ]);

        $this->assertEquals('politics,economy,breaking', $agencyNews->tags);
        $this->assertIsArray($agencyNews->tags_array);
        $this->assertContains('politics', $agencyNews->tags_array);
    }

    public function test_agency_news_can_have_sites()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'News for Sites',
            'agency_id' => 1,
            'sites' => [1, 2, 3],
        ]);

        $this->assertIsArray($agencyNews->sites);
        $this->assertContains(1, $agencyNews->sites);
        $this->assertContains(2, $agencyNews->sites);
        $this->assertContains(3, $agencyNews->sites);
    }

    public function test_agency_news_can_be_converted_to_post()
    {
        $agencyNews = AgencyNews::create([
            'title' => 'Convertible News',
            'summary' => 'Summary for conversion',
            'content' => 'Content for conversion',
            'agency_id' => 1,
            'has_image' => true,
        ]);

        $postData = $agencyNews->convertToPost();

        $this->assertIsArray($postData);
        $this->assertEquals('Convertible News', $postData['title']);
        $this->assertEquals('news', $postData['post_type']);
        $this->assertEquals('draft', $postData['status']);
        $this->assertTrue($postData['is_photo']);
    }

    public function test_agency_news_scope_with_image()
    {
        AgencyNews::create([
            'title' => 'News with Image',
            'agency_id' => 1,
            'has_image' => true,
        ]);

        AgencyNews::create([
            'title' => 'News without Image',
            'agency_id' => 1,
            'has_image' => false,
        ]);

        $withImageCount = AgencyNews::withImage()->count();
        $this->assertEquals(1, $withImageCount);
    }

    public function test_agency_news_scope_by_agency()
    {
        AgencyNews::create([
            'title' => 'News from Agency 1',
            'agency_id' => 1,
        ]);

        AgencyNews::create([
            'title' => 'News from Agency 2',
            'agency_id' => 2,
        ]);

        $agency1Count = AgencyNews::byAgency(1)->count();
        $this->assertEquals(1, $agency1Count);
    }
}
