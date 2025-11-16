<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Posts\Models\Post;
use Tests\TestCase;

class PostModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data manually
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'status' => 'active',
            'type' => 'news',
        ]);

        // Post model uses user_id as author_id, not authors table
    }

    public function test_post_can_be_created()
    {
        $postData = [
            'title' => 'Test Post Title',
            'slug' => 'test-post-title',
            'content' => 'This is test content for the post.',
            'post_type' => 'news',
            'status' => 'published',
            'author_id' => $this->user->id,
            'created_by' => $this->user->id,
        ];

        $post = Post::create($postData);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post Title', $post->title);
        $this->assertEquals('news', $post->post_type);
        $this->assertEquals('published', $post->status);
    }

    public function test_post_can_be_updated()
    {
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $post->update([
            'title' => 'Updated Title',
            'status' => 'published',
        ]);

        $this->assertEquals('Updated Title', $post->fresh()->title);
        $this->assertEquals('published', $post->fresh()->status);
    }

    public function test_post_can_be_deleted()
    {
        $post = Post::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $postId = $post->post_id;
        $post->delete();

        $this->assertSoftDeleted('posts', ['post_id' => $postId]);
    }

    public function test_post_has_required_attributes()
    {
        $post = Post::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($post->post_id);
        $this->assertNotNull($post->title);
        $this->assertNotNull($post->post_type);
        $this->assertNotNull($post->status);
        $this->assertNotNull($post->created_at);
    }

    public function test_post_belongs_to_author()
    {
        $post = Post::factory()->create([
            'author_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $post->author);
        $this->assertEquals($this->user->id, $post->author->id);
    }

    public function test_post_can_be_featured()
    {
        $post = Post::factory()->create([
            'is_mainpage' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($post->is_mainpage);
    }

    public function test_post_can_have_comments()
    {
        $post = Post::factory()->create([
            'is_comment' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($post->is_comment);
    }

    public function test_post_has_view_count()
    {
        $post = Post::factory()->create([
            'view_count' => 100,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(100, $post->view_count);
    }

    public function test_post_can_be_published()
    {
        $post = Post::factory()->create([
            'status' => 'draft',
            'published_date' => null,
            'created_by' => $this->user->id,
        ]);

        $post->update([
            'status' => 'published',
            'published_date' => now(),
        ]);

        $this->assertEquals('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_date);
    }
}
