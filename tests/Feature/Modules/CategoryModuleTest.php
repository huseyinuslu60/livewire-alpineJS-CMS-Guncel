<?php

namespace Tests\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;

class CategoryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created()
    {
        $categoryData = [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'meta_title' => 'Test Category Meta Title',
            'meta_description' => 'Test category meta description',
            'status' => 'active',
            'type' => 'news',
            'show_in_menu' => true,
            'weight' => 1,
        ];

        $category = Category::create($categoryData);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
        $this->assertEquals('active', $category->status);
        $this->assertTrue($category->show_in_menu);
    }

    public function test_category_can_be_updated()
    {
        $category = Category::create([
            'name' => 'Original Name',
            'slug' => 'original-name',
            'status' => 'inactive',
            'type' => 'news',
        ]);

        $category->update([
            'name' => 'Updated Name',
            'status' => 'active',
        ]);

        $this->assertEquals('Updated Name', $category->fresh()->name);
        $this->assertEquals('active', $category->fresh()->status);
    }

    public function test_category_can_be_deleted()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'status' => 'active',
            'type' => 'news',
        ]);
        $categoryId = $category->category_id;

        $category->delete();

        $this->assertDatabaseMissing('categories', ['category_id' => $categoryId]);
    }

    public function test_category_has_required_attributes()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'status' => 'active',
            'type' => 'news',
        ]);

        $this->assertNotNull($category->category_id);
        $this->assertNotNull($category->name);
        $this->assertNotNull($category->slug);
        $this->assertNotNull($category->status);
        $this->assertNotNull($category->type);
        $this->assertNotNull($category->created_at);
    }

    public function test_category_slug_is_auto_generated()
    {
        $category = Category::create([
            'name' => 'Test Category Name',
            'status' => 'active',
            'type' => 'news',
        ]);

        $this->assertEquals('test-category-name', $category->slug);
    }

    public function test_category_can_have_parent()
    {
        $parentCategory = Category::create([
            'name' => 'Parent Category',
            'slug' => 'parent-category',
            'status' => 'active',
            'type' => 'news',
        ]);

        $childCategory = Category::create([
            'name' => 'Child Category',
            'slug' => 'child-category',
            'status' => 'active',
            'type' => 'news',
            'parent_id' => $parentCategory->category_id,
        ]);

        $this->assertEquals($parentCategory->category_id, $childCategory->parent_id);
        $this->assertInstanceOf(Category::class, $childCategory->parent);
    }

    public function test_category_can_have_children()
    {
        $parentCategory = Category::create([
            'name' => 'Parent Category',
            'slug' => 'parent-category',
            'status' => 'active',
            'type' => 'news',
        ]);

        $childCategory = Category::create([
            'name' => 'Child Category',
            'slug' => 'child-category',
            'status' => 'active',
            'type' => 'news',
            'parent_id' => $parentCategory->category_id,
        ]);

        $this->assertTrue($parentCategory->children->contains($childCategory));
    }

    public function test_category_can_be_shown_in_menu()
    {
        $category = Category::create([
            'name' => 'Menu Category',
            'slug' => 'menu-category',
            'status' => 'active',
            'type' => 'news',
            'show_in_menu' => true,
        ]);

        $this->assertTrue($category->show_in_menu);
    }

    public function test_category_has_weight_for_ordering()
    {
        $category = Category::create([
            'name' => 'Weighted Category',
            'slug' => 'weighted-category',
            'status' => 'active',
            'type' => 'news',
            'weight' => 5,
        ]);

        $this->assertEquals(5, $category->weight);
    }

    public function test_category_has_meta_information()
    {
        $category = Category::create([
            'name' => 'Meta Category',
            'slug' => 'meta-category',
            'status' => 'active',
            'type' => 'news',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'keyword1, keyword2',
        ]);

        $this->assertEquals('Meta Title', $category->meta_title);
        $this->assertEquals('Meta Description', $category->meta_description);
        $this->assertEquals('keyword1, keyword2', $category->meta_keywords);
    }
}
