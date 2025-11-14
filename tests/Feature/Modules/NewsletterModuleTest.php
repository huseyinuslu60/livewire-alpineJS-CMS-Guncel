<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Newsletters\Models\Newsletter;
use Modules\Newsletters\Models\NewsletterUser;
use Tests\TestCase;

class NewsletterModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_newsletter_can_be_created()
    {
        $newsletterData = [
            'name' => 'Test Newsletter',
            'status' => 'draft',
            'mail_status' => 'pending',
            'mail_subject' => 'Test Subject',
            'mail_body' => 'Test Body',
            'mail_body_raw' => 'Test Raw Body',
            'created_by' => $this->user->id,
            'success_count' => 0,
            'total_count' => 0,
            'reklam' => false,
        ];

        $newsletter = Newsletter::create($newsletterData);

        $this->assertInstanceOf(Newsletter::class, $newsletter);
        $this->assertEquals('Test Newsletter', $newsletter->name);
        $this->assertEquals('draft', $newsletter->status);
        $this->assertEquals('pending', $newsletter->mail_status);
        $this->assertFalse($newsletter->reklam);
        $this->assertDatabaseHas('newsletters', ['name' => 'Test Newsletter']);
    }

    public function test_newsletter_can_be_updated()
    {
        $newsletter = Newsletter::create([
            'name' => 'Original Newsletter',
            'status' => 'draft',
            'mail_status' => 'pending',
            'mail_subject' => 'Original Subject',
            'mail_body' => 'Original Body',
            'created_by' => $this->user->id,
        ]);

        $newsletter->update([
            'status' => 'sent',
            'mail_status' => 'completed',
        ]);

        $this->assertEquals('sent', $newsletter->fresh()->status);
        $this->assertEquals('completed', $newsletter->fresh()->mail_status);
    }

    public function test_newsletter_can_be_deleted()
    {
        $newsletter = Newsletter::create([
            'name' => 'Newsletter to Delete',
            'status' => 'draft',
            'mail_status' => 'pending',
            'mail_subject' => 'Delete Subject',
            'mail_body' => 'Delete Body',
            'created_by' => $this->user->id,
        ]);
        $newsletterId = $newsletter->newsletter_id;

        $newsletter->delete();

        $this->assertDatabaseMissing('newsletters', ['newsletter_id' => $newsletterId]);
    }

    public function test_newsletter_has_required_attributes()
    {
        $newsletter = Newsletter::create([
            'name' => 'Required Newsletter',
            'status' => 'draft',
            'mail_status' => 'pending',
            'mail_subject' => 'Required Subject',
            'mail_body' => 'Required Body',
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($newsletter->newsletter_id);
        $this->assertNotNull($newsletter->name);
        $this->assertNotNull($newsletter->status);
        $this->assertNotNull($newsletter->mail_status);
        $this->assertNotNull($newsletter->created_at);
    }

    public function test_newsletter_can_have_mail_content()
    {
        $newsletter = Newsletter::create([
            'name' => 'Newsletter with Content',
            'status' => 'draft',
            'mail_status' => 'pending',
            'mail_subject' => 'Newsletter Subject',
            'mail_body' => '<h1>Newsletter Body</h1>',
            'mail_body_raw' => 'Newsletter Raw Body',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('Newsletter Subject', $newsletter->mail_subject);
        $this->assertEquals('<h1>Newsletter Body</h1>', $newsletter->mail_body);
        $this->assertEquals('Newsletter Raw Body', $newsletter->mail_body_raw);
    }

    public function test_newsletter_can_track_success()
    {
        $newsletter = Newsletter::create([
            'name' => 'Newsletter with Stats',
            'status' => 'sent',
            'mail_status' => 'completed',
            'mail_subject' => 'Stats Subject',
            'mail_body' => 'Stats Body',
            'success_count' => 10,
            'total_count' => 15,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(10, $newsletter->success_count);
        $this->assertEquals(15, $newsletter->total_count);
    }

    public function test_newsletter_can_be_advertisement()
    {
        $newsletter = Newsletter::create([
            'name' => 'Ad Newsletter',
            'status' => 'draft',
            'mail_status' => 'pending',
            'mail_subject' => 'Ad Subject',
            'mail_body' => 'Ad Body',
            'reklam' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($newsletter->reklam);
    }

    public function test_newsletter_user_can_be_created()
    {
        $newsletterUserData = [
            'name' => 'Test User',
            'email' => 'newsletter@example.com',
            'status' => 'active',
            'email_status' => 'verified',
            'hash_code' => 'test-hash-code',
            'verify_status' => 'verified',
        ];

        $newsletterUser = NewsletterUser::create($newsletterUserData);

        $this->assertInstanceOf(NewsletterUser::class, $newsletterUser);
        $this->assertEquals('Test User', $newsletterUser->name);
        $this->assertEquals('newsletter@example.com', $newsletterUser->email);
        $this->assertEquals('active', $newsletterUser->status);
        $this->assertEquals('verified', $newsletterUser->email_status);
        $this->assertDatabaseHas('newsletter_users', ['email' => 'newsletter@example.com']);
    }

    public function test_newsletter_user_can_be_updated()
    {
        $newsletterUser = NewsletterUser::create([
            'name' => 'Original User',
            'email' => 'original@example.com',
            'status' => 'inactive',
            'email_status' => 'unverified',
            'hash_code' => 'original-hash',
        ]);

        $newsletterUser->update([
            'status' => 'active',
            'email_status' => 'verified',
        ]);

        $this->assertEquals('active', $newsletterUser->fresh()->status);
        $this->assertEquals('verified', $newsletterUser->fresh()->email_status);
    }

    public function test_newsletter_user_can_be_deleted()
    {
        $newsletterUser = NewsletterUser::create([
            'name' => 'User to Delete',
            'email' => 'delete@example.com',
            'status' => 'active',
            'email_status' => 'verified',
            'hash_code' => 'delete-hash',
        ]);
        $userId = $newsletterUser->user_id;

        $newsletterUser->delete();

        $this->assertDatabaseMissing('newsletter_users', ['user_id' => $userId]);
    }

    public function test_newsletter_user_has_required_attributes()
    {
        $newsletterUser = NewsletterUser::create([
            'name' => 'Required User',
            'email' => 'required@example.com',
            'status' => 'active',
            'email_status' => 'verified',
            'hash_code' => 'required-hash',
        ]);

        $this->assertNotNull($newsletterUser->user_id);
        $this->assertNotNull($newsletterUser->name);
        $this->assertNotNull($newsletterUser->email);
        $this->assertNotNull($newsletterUser->status);
        $this->assertNotNull($newsletterUser->email_status);
        $this->assertNotNull($newsletterUser->created_at);
    }
}
