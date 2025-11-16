<?php

namespace Tests\Unit;

use Modules\Posts\Enums\PostPosition;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Enums\PostType;
use Tests\TestCase;

class PostEnumsTest extends TestCase
{
    public function test_post_status_enum_values()
    {
        $this->assertEquals('draft', PostStatus::Draft->value);
        $this->assertEquals('published', PostStatus::Published->value);
        $this->assertEquals('scheduled', PostStatus::Scheduled->value);
        $this->assertEquals('archived', PostStatus::Archived->value);
    }

    public function test_post_status_options()
    {
        $options = PostStatus::options();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('draft', $options);
        $this->assertArrayHasKey('published', $options);
        $this->assertEquals('Pasif', $options['draft']);
        $this->assertEquals('Aktif', $options['published']);
    }

    public function test_post_status_label()
    {
        $this->assertEquals('Pasif', PostStatus::label('draft'));
        $this->assertEquals('Aktif', PostStatus::label('published'));
        $this->assertEquals('Zamanlanmış', PostStatus::label('scheduled'));
    }

    public function test_post_type_enum_values()
    {
        $this->assertEquals('news', PostType::News->value);
        $this->assertEquals('gallery', PostType::Gallery->value);
        $this->assertEquals('video', PostType::Video->value);
    }

    public function test_post_type_options()
    {
        $options = PostType::options();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('news', $options);
        $this->assertArrayHasKey('gallery', $options);
        $this->assertEquals('Haber', $options['news']);
        $this->assertEquals('Galeri', $options['gallery']);
    }

    public function test_post_position_enum_values()
    {
        $this->assertEquals('normal', PostPosition::Normal->value);
        $this->assertEquals('manşet', PostPosition::Manset->value);
        $this->assertEquals('sürmanşet', PostPosition::Surmanset->value);
        $this->assertEquals('öne çıkanlar', PostPosition::OneCikanlar->value);
    }

    public function test_post_position_options()
    {
        $options = PostPosition::options();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('normal', $options);
        $this->assertArrayHasKey('manşet', $options);
        $this->assertEquals('Normal', $options['normal']);
        $this->assertEquals('Manşet', $options['manşet']);
    }

    public function test_post_position_to_zone()
    {
        $this->assertEquals('manset', PostPosition::toZone('manşet'));
        $this->assertEquals('surmanset', PostPosition::toZone('sürmanşet'));
        $this->assertEquals('one_cikanlar', PostPosition::toZone('öne çıkanlar'));
        $this->assertNull(PostPosition::toZone('normal'));
    }
}
