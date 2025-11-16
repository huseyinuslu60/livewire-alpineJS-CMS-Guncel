<?php

use App\Models\User;
use App\Support\Sanitizer;
use Modules\AgencyNews\Models\AgencyNews;
use Modules\Articles\Models\Article;
use Modules\Posts\Models\Post;

use function Pest\Laravel\actingAs;

describe('Sanitizer', function () {
    it('removes script tags', function () {
        $malicious = '<script>alert("XSS")</script><p>Safe content</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, '<script>'))->toBeFalse();
        expect(str_contains($sanitized, 'alert'))->toBeFalse();
        expect(str_contains($sanitized, '<p>Safe content</p>'))->toBeTrue();
    });

    it('removes iframe tags', function () {
        $malicious = '<iframe src="evil.com"></iframe><p>Safe</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, '<iframe>'))->toBeFalse();
        expect(str_contains($sanitized, '<p>Safe</p>'))->toBeTrue();
    });

    it('removes javascript: protocol from href', function () {
        $malicious = '<a href="javascript:alert(\'XSS\')">Click</a>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, 'javascript:'))->toBeFalse();
        expect(str_contains($sanitized, 'alert'))->toBeFalse();
    });

    it('removes data: protocol from src', function () {
        $malicious = '<img src="data:text/html,<script>alert(1)</script>">';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, 'data:'))->toBeFalse();
        expect(str_contains($sanitized, 'alert'))->toBeFalse();
    });

    it('removes event handlers like onclick', function () {
        $malicious = '<p onclick="alert(\'XSS\')">Click me</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, 'onclick'))->toBeFalse();
        expect(str_contains($sanitized, 'alert'))->toBeFalse();
        expect(str_contains($sanitized, '<p>Click me</p>'))->toBeTrue();
    });

    it('removes style attributes to prevent CSS injection', function () {
        $malicious = '<p style="background: url(javascript:alert(1))">Text</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, 'style='))->toBeFalse();
        expect(str_contains($sanitized, 'javascript:'))->toBeFalse();
    });

    it('preserves safe HTML tags', function () {
        $safe = '<p>Paragraph</p><strong>Bold</strong><em>Italic</em><ul><li>Item</li></ul>';
        $sanitized = Sanitizer::sanitizeHtml($safe);

        expect($sanitized)
            ->toContain('<p>')
            ->toContain('<strong>')
            ->toContain('<em>')
            ->toContain('<ul>')
            ->toContain('<li>');
    });

    it('preserves safe links with http/https', function () {
        $safe = '<a href="https://example.com">Link</a>';
        $sanitized = Sanitizer::sanitizeHtml($safe);

        expect($sanitized)
            ->toContain('href="https://example.com"')
            ->toContain('rel="noopener"');
    });

    it('adds noopener to links with target="_blank"', function () {
        $link = '<a href="https://example.com" target="_blank">Link</a>';
        $sanitized = Sanitizer::sanitizeHtml($link);

        expect($sanitized)
            ->toContain('target="_blank"')
            ->toContain('rel="noopener"');
    });

    it('handles empty input', function () {
        expect(Sanitizer::sanitizeHtml(null))->toBe('');
        expect(Sanitizer::sanitizeHtml(''))->toBe('');
    });

    it('removes object and embed tags', function () {
        $malicious = '<object data="evil.swf"></object><embed src="evil.swf">';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)->toBeString();
        expect(str_contains($sanitized, '<object>'))->toBeFalse();
        expect(str_contains($sanitized, '<embed>'))->toBeFalse();
    });
});

describe('Post Model Sanitization', function () {
    it('sanitizes content when creating a post', function () {
        $user = User::factory()->create();
        actingAs($user);

        $malicious = '<script>alert("XSS")</script><p>Safe content</p>';

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post-'.time(),
            'content' => $malicious,
            'author_id' => $user->id,
            'post_type' => 'news',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        expect($post->content)->toBeString();
        expect(str_contains($post->content, '<script>'))->toBeFalse();
        expect(str_contains($post->content, 'alert'))->toBeFalse();
        expect(str_contains($post->content, '<p>Safe content</p>'))->toBeTrue();
    });

    it('sanitizes content when updating a post', function () {
        $user = User::factory()->create();
        actingAs($user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $malicious = '<script>alert("XSS")</script><p>Updated</p>';
        $post->update(['content' => $malicious]);

        $freshContent = $post->fresh()->content;
        expect($freshContent)->toBeString();
        expect(str_contains($freshContent, '<script>'))->toBeFalse();
        expect(str_contains($freshContent, 'alert'))->toBeFalse();
        expect(str_contains($freshContent, '<p>Updated</p>'))->toBeTrue();
    });
});

describe('Article Model Sanitization', function () {
    it('sanitizes article_text when creating an article', function () {
        $user = User::factory()->create();
        actingAs($user);

        $malicious = '<script>alert("XSS")</script><p>Safe article</p>';

        $article = Article::create([
            'title' => 'Test Article',
            'article_text' => $malicious,
            'status' => 'draft',
            'author_id' => $user->id,
            'created_by' => $user->id,
        ]);

        expect($article->article_text)->toBeString();
        expect(str_contains($article->article_text, '<script>'))->toBeFalse();
        expect(str_contains($article->article_text, 'alert'))->toBeFalse();
        expect(str_contains($article->article_text, '<p>Safe article</p>'))->toBeTrue();
    });

    it('sanitizes article_text when updating an article', function () {
        $user = User::factory()->create();
        actingAs($user);

        $article = Article::factory()->create([
            'author_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $malicious = '<iframe src="evil.com"></iframe><p>Updated</p>';
        $article->update(['article_text' => $malicious]);

        $freshText = $article->fresh()->article_text;
        expect($freshText)->toBeString();
        expect(str_contains($freshText, '<iframe>'))->toBeFalse();
        expect(str_contains($freshText, '<p>Updated</p>'))->toBeTrue();
    });
});

describe('AgencyNews Model Sanitization', function () {
    it('sanitizes content when creating agency news', function () {
        $malicious = '<script>alert("XSS")</script><p>Safe news</p>';

        $agencyNews = AgencyNews::create([
            'title' => 'Test News',
            'content' => $malicious,
            'agency_id' => 1,
        ]);

        expect($agencyNews->content)->toBeString();
        expect(str_contains($agencyNews->content, '<script>'))->toBeFalse();
        expect(str_contains($agencyNews->content, 'alert'))->toBeFalse();
        expect(str_contains($agencyNews->content, '<p>Safe news</p>'))->toBeTrue();
    });

    it('sanitizes content when updating agency news', function () {
        $agencyNews = AgencyNews::factory()->create();

        $malicious = '<object data="evil.swf"></object><p>Updated</p>';
        $agencyNews->update(['content' => $malicious]);

        $freshContent = $agencyNews->fresh()->content;
        expect($freshContent)->toBeString();
        expect(str_contains($freshContent, '<object>'))->toBeFalse();
        expect(str_contains($freshContent, '<p>Updated</p>'))->toBeTrue();
    });
});
