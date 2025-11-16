<?php

use App\Support\Sanitizer;
use Modules\Articles\Models\Article;
use Modules\AgencyNews\Models\AgencyNews;
use Modules\Posts\Models\Post;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = \App\Models\User::factory()->create();
});

describe('Sanitizer', function () {
    it('removes script tags', function () {
        $malicious = '<script>alert("XSS")</script><p>Safe content</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)
            ->not->toContain('<script>')
            ->not->toContain('alert')
            ->toContain('<p>Safe content</p>');
    });

    it('removes iframe tags', function () {
        $malicious = '<iframe src="evil.com"></iframe><p>Safe</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)
            ->not->toContain('<iframe>')
            ->toContain('<p>Safe</p>');
    });

    it('removes javascript: protocol from href', function () {
        $malicious = '<a href="javascript:alert(\'XSS\')">Click</a>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)
            ->not->toContain('javascript:')
            ->not->toContain('alert');
    });

    it('removes data: protocol from src', function () {
        $malicious = '<img src="data:text/html,<script>alert(1)</script>">';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)
            ->not->toContain('data:')
            ->not->toContain('alert');
    });

    it('removes event handlers like onclick', function () {
        $malicious = '<p onclick="alert(\'XSS\')">Click me</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)
            ->not->toContain('onclick')
            ->not->toContain('alert')
            ->toContain('<p>Click me</p>');
    });

    it('removes style attributes to prevent CSS injection', function () {
        $malicious = '<p style="background: url(javascript:alert(1))">Text</p>';
        $sanitized = Sanitizer::sanitizeHtml($malicious);

        expect($sanitized)
            ->not->toContain('style=')
            ->not->toContain('javascript:');
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

        expect($sanitized)
            ->not->toContain('<object>')
            ->not->toContain('<embed>');
    });
});

describe('Post Model Sanitization', function () {
    it('sanitizes content when creating a post', function () {
        actingAs($this->user);

        $malicious = '<script>alert("XSS")</script><p>Safe content</p>';

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post-'.time(),
            'content' => $malicious,
            'author_id' => $this->user->id,
            'post_type' => 'news',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        expect($post->content)
            ->not->toContain('<script>')
            ->not->toContain('alert')
            ->toContain('<p>Safe content</p>');
    });

    it('sanitizes content when updating a post', function () {
        actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $malicious = '<script>alert("XSS")</script><p>Updated</p>';
        $post->update(['content' => $malicious]);

        expect($post->fresh()->content)
            ->not->toContain('<script>')
            ->not->toContain('alert')
            ->toContain('<p>Updated</p>');
    });
});

describe('Article Model Sanitization', function () {
    it('sanitizes article_text when creating an article', function () {
        actingAs($this->user);

        $malicious = '<script>alert("XSS")</script><p>Safe article</p>';

        $article = Article::create([
            'title' => 'Test Article',
            'article_text' => $malicious,
            'status' => 'draft',
            'author_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        expect($article->article_text)
            ->not->toContain('<script>')
            ->not->toContain('alert')
            ->toContain('<p>Safe article</p>');
    });

    it('sanitizes article_text when updating an article', function () {
        actingAs($this->user);

        $article = Article::factory()->create([
            'author_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $malicious = '<iframe src="evil.com"></iframe><p>Updated</p>';
        $article->update(['article_text' => $malicious]);

        expect($article->fresh()->article_text)
            ->not->toContain('<iframe>')
            ->toContain('<p>Updated</p>');
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

        expect($agencyNews->content)
            ->not->toContain('<script>')
            ->not->toContain('alert')
            ->toContain('<p>Safe news</p>');
    });

    it('sanitizes content when updating agency news', function () {
        $agencyNews = AgencyNews::factory()->create();

        $malicious = '<object data="evil.swf"></object><p>Updated</p>';
        $agencyNews->update(['content' => $malicious]);

        expect($agencyNews->fresh()->content)
            ->not->toContain('<object>')
            ->toContain('<p>Updated</p>');
    });
});

