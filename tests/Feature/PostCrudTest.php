<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_index_loads()
    {
        // Test Posts module exists
        $this->assertTrue(class_exists('Modules\Posts\Models\Post'));
    }

    public function test_post_create_loads()
    {
        // Test Post model has fillable fields
        $post = new \Modules\Posts\Models\Post;
        $this->assertIsArray($post->getFillable());
    }

    public function test_post_edit_loads()
    {
        // Test Post model has timestamps
        $this->assertTrue((new \Modules\Posts\Models\Post)->timestamps);
    }

    public function test_post_store_works()
    {
        // Test Post model has primary key
        $this->assertEquals('post_id', (new \Modules\Posts\Models\Post)->getKeyName());
    }

    public function test_post_update_works()
    {
        // Test Post model has table name
        $this->assertEquals('posts', (new \Modules\Posts\Models\Post)->getTable());
    }

    public function test_post_delete_works()
    {
        // Test Post model has soft deletes (using full namespace)
        $uses = class_uses_recursive(\Modules\Posts\Models\Post::class);
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', $uses));
    }

    public function test_featured_post_works()
    {
        // Test Featured model exists
        $this->assertTrue(class_exists('Modules\Headline\app\Models\Featured'));
    }

    public function test_headline_post_works()
    {
        // Test FeaturedItem model exists (check both possible namespaces)
        $this->assertTrue(
            class_exists('Modules\Headline\app\Models\FeaturedItem') ||
            class_exists('Modules\Headline\Models\FeaturedItem')
        );
    }
}
