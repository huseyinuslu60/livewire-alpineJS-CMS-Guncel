<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Articles\Models\Article;
use Modules\Authors\Models\Author;
use Tests\TestCase;

class ArticleModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Author $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();

        $this->actingAs($this->user);
    }

    public function test_article_can_be_created()
    {
        $articleData = [
            'title' => 'Test Article Title',
            'summary' => 'This is a test article summary.',
            'article_text' => 'This is the full content of the test article.',
            'author_id' => $this->author->id,
            'status' => 'published',
            'show_on_mainpage' => true,
            'is_commentable' => true,
            'published_at' => now(),
            'created_by' => $this->user->id,
        ];

        $article = Article::create($articleData);

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Test Article Title', $article->title);
        $this->assertEquals('published', $article->status);
        $this->assertTrue($article->show_on_mainpage);
        $this->assertTrue($article->is_commentable);
    }

    public function test_article_can_be_updated()
    {
        $article = Article::factory()->create([
            'title' => 'Original Title',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $article->update([
            'title' => 'Updated Title',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertEquals('Updated Title', $article->fresh()->title);
        $this->assertEquals('published', $article->fresh()->status);
        $this->assertNotNull($article->fresh()->published_at);
    }

    public function test_article_can_be_deleted()
    {
        $article = Article::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $articleId = $article->article_id;
        $article->delete();

        $this->assertSoftDeleted('articles', ['article_id' => $articleId]);
    }

    public function test_article_has_required_attributes()
    {
        $article = Article::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($article->article_id);
        $this->assertNotNull($article->title);
        $this->assertNotNull($article->status);
        $this->assertNotNull($article->created_at);
    }

    public function test_article_belongs_to_author()
    {
        $article = Article::factory()->create([
            'author_id' => $this->author->id,
            'created_by' => $this->user->id,
        ]);

        $article->load('author');

        $this->assertInstanceOf(Author::class, $article->author);
        $this->assertEquals($this->author->id, $article->author->id);
    }

    public function test_article_can_be_published()
    {
        $article = Article::factory()->create([
            'status' => 'draft',
            'published_at' => null,
            'created_by' => $this->user->id,
        ]);

        $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertEquals('published', $article->fresh()->status);
        $this->assertNotNull($article->fresh()->published_at);
    }

    public function test_article_hit_count_increases()
    {
        $article = Article::factory()->create([
            'hit' => 0,
            'created_by' => $this->user->id,
        ]);

        $article->increment('hit');

        $this->assertEquals(1, $article->fresh()->hit);
    }

    public function test_article_can_be_featured_on_mainpage()
    {
        $article = Article::factory()->create([
            'show_on_mainpage' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($article->show_on_mainpage);
    }

    public function test_article_can_have_comments()
    {
        $article = Article::factory()->create([
            'is_commentable' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($article->is_commentable);
    }
}
