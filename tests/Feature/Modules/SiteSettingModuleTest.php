<?php

namespace Tests\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Settings\Models\SiteSetting;
use Tests\TestCase;

class SiteSettingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_setting_can_be_created()
    {
        $settingData = [
            'key' => 'site_title',
            'value' => 'My Website',
            'type' => 'text',
            'group' => 'general',
            'label' => 'Site Title',
            'description' => 'The main title of the website',
            'options' => null,
            'sort_order' => 1,
            'is_required' => true,
            'is_active' => true,
        ];

        $setting = SiteSetting::create($settingData);

        $this->assertInstanceOf(SiteSetting::class, $setting);
        $this->assertEquals('site_title', $setting->key);
        $this->assertEquals('My Website', $setting->value);
        $this->assertEquals('text', $setting->type);
        $this->assertTrue($setting->is_required);
        $this->assertTrue($setting->is_active);
        $this->assertDatabaseHas('site_settings', ['key' => 'site_title']);
    }

    public function test_site_setting_can_be_updated()
    {
        $setting = SiteSetting::create([
            'key' => 'site_title',
            'value' => 'Original Title',
            'type' => 'text',
            'group' => 'general',
            'is_required' => false,
            'is_active' => false,
        ]);

        $setting->update([
            'value' => 'Updated Title',
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->assertEquals('Updated Title', $setting->fresh()->value);
        $this->assertTrue($setting->fresh()->is_required);
        $this->assertTrue($setting->fresh()->is_active);
    }

    public function test_site_setting_can_be_deleted()
    {
        $setting = SiteSetting::create([
            'key' => 'setting_to_delete',
            'value' => 'Value to delete',
            'type' => 'text',
            'group' => 'general',
        ]);
        $settingId = $setting->id;

        $setting->delete();

        $this->assertDatabaseMissing('site_settings', ['id' => $settingId]);
    }

    public function test_site_setting_has_required_attributes()
    {
        $setting = SiteSetting::create([
            'key' => 'required_setting',
            'value' => 'Required Value',
            'type' => 'text',
            'group' => 'general',
        ]);

        $this->assertNotNull($setting->id);
        $this->assertNotNull($setting->key);
        $this->assertNotNull($setting->value);
        $this->assertNotNull($setting->type);
        $this->assertNotNull($setting->group);
        $this->assertNotNull($setting->created_at);
    }

    public function test_site_setting_can_have_options()
    {
        $options = ['option1' => 'Option 1', 'option2' => 'Option 2'];

        $setting = SiteSetting::create([
            'key' => 'setting_with_options',
            'value' => 'option1',
            'type' => 'select',
            'group' => 'general',
            'options' => $options,
        ]);

        $this->assertIsArray($setting->options);
        $this->assertEquals($options, $setting->options);
    }

    public function test_site_setting_scope_active()
    {
        SiteSetting::create([
            'key' => 'active_setting',
            'value' => 'Active Value',
            'type' => 'text',
            'group' => 'general',
            'is_active' => true,
        ]);

        SiteSetting::create([
            'key' => 'inactive_setting',
            'value' => 'Inactive Value',
            'type' => 'text',
            'group' => 'general',
            'is_active' => false,
        ]);

        $activeCount = SiteSetting::active()->count();
        $this->assertEquals(1, $activeCount);
    }

    public function test_site_setting_scope_by_group()
    {
        SiteSetting::create([
            'key' => 'general_setting',
            'value' => 'General Value',
            'type' => 'text',
            'group' => 'general',
        ]);

        SiteSetting::create([
            'key' => 'seo_setting',
            'value' => 'SEO Value',
            'type' => 'text',
            'group' => 'seo',
        ]);

        $generalCount = SiteSetting::byGroup('general')->count();
        $this->assertEquals(1, $generalCount);
    }

    public function test_site_setting_scope_ordered()
    {
        SiteSetting::create([
            'key' => 'second_setting',
            'value' => 'Second Value',
            'type' => 'text',
            'group' => 'general',
            'sort_order' => 2,
            'label' => 'Second Setting',
        ]);

        SiteSetting::create([
            'key' => 'first_setting',
            'value' => 'First Value',
            'type' => 'text',
            'group' => 'general',
            'sort_order' => 1,
            'label' => 'First Setting',
        ]);

        $orderedSettings = SiteSetting::ordered()->get();
        $this->assertEquals('first_setting', $orderedSettings->first()->key);
        $this->assertEquals('second_setting', $orderedSettings->last()->key);
    }

    public function test_site_setting_can_be_required()
    {
        $setting = SiteSetting::create([
            'key' => 'required_setting',
            'value' => 'Required Value',
            'type' => 'text',
            'group' => 'general',
            'is_required' => true,
        ]);

        $this->assertTrue($setting->is_required);
    }

    public function test_site_setting_can_have_description()
    {
        $setting = SiteSetting::create([
            'key' => 'described_setting',
            'value' => 'Described Value',
            'type' => 'text',
            'group' => 'general',
            'description' => 'This is a described setting',
        ]);

        $this->assertEquals('This is a described setting', $setting->description);
    }
}
