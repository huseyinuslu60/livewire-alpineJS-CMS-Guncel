<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Comments\Models\Comment;
use Modules\Posts\Models\Post;
use Tests\TestCase;

class CommentModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test Post Content',
            'post_type' => 'news',
            'post_position' => 'normal',
            'status' => 'published',
            'author_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_comment_can_be_created()
    {
        $commentData = [
            'post_id' => $this->post->post_id,
            'name' => 'Test Commenter',
            'comment_text' => 'This is a test comment',
            'status' => 'pending',
            'ip_address' => '192.168.1.1',
            'up_vote' => 0,
            'down_vote' => 0,
            'email' => 'commenter@example.com',
        ];

        $comment = Comment::create($commentData);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test Commenter', $comment->name);
        $this->assertEquals('This is a test comment', $comment->comment_text);
        $this->assertEquals('pending', $comment->status);
        $this->assertDatabaseHas('comments', ['name' => 'Test Commenter']);
    }

    public function test_comment_can_be_updated()
    {
        $comment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Original Commenter',
            'comment_text' => 'Original comment',
            'status' => 'pending',
        ]);

        $comment->update([
            'comment_text' => 'Updated comment',
            'status' => 'approved',
        ]);

        $this->assertEquals('Updated comment', $comment->fresh()->comment_text);
        $this->assertEquals('approved', $comment->fresh()->status);
    }

    public function test_comment_can_be_deleted()
    {
        $comment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Comment to Delete',
            'comment_text' => 'Comment to delete',
            'status' => 'pending',
        ]);
        $commentId = $comment->comment_id;

        $comment->delete();

        $this->assertSoftDeleted('comments', ['comment_id' => $commentId]);
    }

    public function test_comment_has_required_attributes()
    {
        $comment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Required Commenter',
            'comment_text' => 'Required comment',
            'status' => 'pending',
        ]);

        $this->assertNotNull($comment->comment_id);
        $this->assertNotNull($comment->post_id);
        $this->assertNotNull($comment->name);
        $this->assertNotNull($comment->comment_text);
        $this->assertNotNull($comment->status);
        $this->assertNotNull($comment->created_at);
    }

    public function test_comment_can_have_votes()
    {
        $comment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Voted Commenter',
            'comment_text' => 'Comment with votes',
            'status' => 'approved',
            'up_vote' => 5,
            'down_vote' => 2,
        ]);

        $this->assertEquals(5, $comment->up_vote);
        $this->assertEquals(2, $comment->down_vote);
    }

    public function test_comment_can_have_parent()
    {
        $parentComment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Parent Commenter',
            'comment_text' => 'Parent comment',
            'status' => 'approved',
        ]);

        $childComment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Child Commenter',
            'comment_text' => 'Child comment',
            'status' => 'approved',
            'parent_id' => $parentComment->comment_id,
        ]);

        $this->assertEquals($parentComment->comment_id, $childComment->parent_id);
        $this->assertInstanceOf(Comment::class, $childComment->parent);
    }

    public function test_comment_can_have_replies()
    {
        $parentComment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Parent Commenter',
            'comment_text' => 'Parent comment',
            'status' => 'approved',
        ]);

        $childComment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Child Commenter',
            'comment_text' => 'Child comment',
            'status' => 'approved',
            'parent_id' => $parentComment->comment_id,
        ]);

        $this->assertTrue($parentComment->replies->contains($childComment));
    }

    public function test_comment_scope_approved()
    {
        Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Approved Commenter',
            'comment_text' => 'Approved comment',
            'status' => 'approved',
        ]);

        Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Pending Commenter',
            'comment_text' => 'Pending comment',
            'status' => 'pending',
        ]);

        $approvedCount = Comment::approved()->count();
        $this->assertEquals(1, $approvedCount);
    }

    public function test_comment_scope_pending()
    {
        Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Approved Commenter',
            'comment_text' => 'Approved comment',
            'status' => 'approved',
        ]);

        Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Pending Commenter',
            'comment_text' => 'Pending comment',
            'status' => 'pending',
        ]);

        $pendingCount = Comment::pending()->count();
        $this->assertEquals(1, $pendingCount);
    }

    public function test_comment_scope_rejected()
    {
        Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Rejected Commenter',
            'comment_text' => 'Rejected comment',
            'status' => 'rejected',
        ]);

        Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Approved Commenter',
            'comment_text' => 'Approved comment',
            'status' => 'approved',
        ]);

        $rejectedCount = Comment::rejected()->count();
        $this->assertEquals(1, $rejectedCount);
    }

    public function test_comment_belongs_to_post()
    {
        $comment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Post Commenter',
            'comment_text' => 'Post comment',
            'status' => 'approved',
        ]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($this->post->post_id, $comment->post->post_id);
    }

    public function test_comment_has_status_badge()
    {
        $approvedComment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Approved Commenter',
            'comment_text' => 'Approved comment',
            'status' => 'approved',
        ]);

        $pendingComment = Comment::create([
            'post_id' => $this->post->post_id,
            'name' => 'Pending Commenter',
            'comment_text' => 'Pending comment',
            'status' => 'pending',
        ]);

        $this->assertEquals('success', $approvedComment->status_badge);
        $this->assertEquals('warning', $pendingComment->status_badge);
    }
}
