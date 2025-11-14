<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Logs\Models\UserLog;
use Tests\TestCase;

class UserLogModuleTest extends TestCase
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

    public function test_user_log_can_be_created()
    {
        $logData = [
            'user_id' => $this->user->id,
            'action' => 'create',
            'model_type' => 'App\\Models\\Post',
            'model_id' => 123,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'description' => 'Test log entry',
            'url' => '/admin/posts',
            'method' => 'POST',
        ];

        $userLog = UserLog::create($logData);

        $this->assertInstanceOf(UserLog::class, $userLog);
        $this->assertEquals('create', $userLog->action);
        $this->assertEquals($this->user->id, $userLog->user_id);
        $this->assertEquals('App\\Models\\Post', $userLog->model_type);
        $this->assertDatabaseHas('user_logs', ['action' => 'create']);
    }

    public function test_user_log_can_be_updated()
    {
        $userLog = UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
            'description' => 'Original description',
        ]);

        $userLog->update([
            'action' => 'update',
            'description' => 'Updated description',
        ]);

        $this->assertEquals('update', $userLog->fresh()->action);
        $this->assertEquals('Updated description', $userLog->fresh()->description);
    }

    public function test_user_log_can_be_deleted()
    {
        $userLog = UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
            'description' => 'Log to delete',
        ]);
        $logId = $userLog->log_id;

        $userLog->delete();

        $this->assertDatabaseMissing('user_logs', ['log_id' => $logId]);
    }

    public function test_user_log_has_required_attributes()
    {
        $userLog = UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
        ]);

        $this->assertNotNull($userLog->log_id);
        $this->assertNotNull($userLog->user_id);
        $this->assertNotNull($userLog->action);
        $this->assertNotNull($userLog->created_at);
    }

    public function test_user_log_can_have_old_and_new_values()
    {
        $oldValues = ['title' => 'Old Title', 'status' => 'draft'];
        $newValues = ['title' => 'New Title', 'status' => 'published'];

        $userLog = UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'update',
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);

        $this->assertIsArray($userLog->old_values);
        $this->assertIsArray($userLog->new_values);
        $this->assertEquals($oldValues, $userLog->old_values);
        $this->assertEquals($newValues, $userLog->new_values);
    }

    public function test_user_log_can_have_metadata()
    {
        $metadata = ['ip' => '192.168.1.1', 'browser' => 'Chrome'];

        $userLog = UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'login',
            'metadata' => $metadata,
        ]);

        $this->assertIsArray($userLog->metadata);
        $this->assertEquals($metadata, $userLog->metadata);
    }

    public function test_user_log_scope_by_action()
    {
        // Clear any existing logs first
        UserLog::truncate();

        UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
        ]);

        UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'update',
        ]);

        $createCount = UserLog::byAction('create')->count();
        $this->assertEquals(1, $createCount);
    }

    public function test_user_log_scope_by_user()
    {
        $anotherUser = User::create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => bcrypt('password'),
        ]);

        UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
        ]);

        UserLog::create([
            'user_id' => $anotherUser->id,
            'action' => 'create',
        ]);

        $userLogsCount = UserLog::byUser($this->user->id)->count();
        $this->assertEquals(1, $userLogsCount);
    }

    public function test_user_log_belongs_to_user()
    {
        $userLog = UserLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
        ]);

        $this->assertInstanceOf(User::class, $userLog->user);
        $this->assertEquals($this->user->id, $userLog->user->id);
    }

    public function test_user_log_has_action_constants()
    {
        $this->assertNotEmpty(UserLog::ACTIONS);
        $this->assertArrayHasKey('create', UserLog::ACTIONS);
        $this->assertArrayHasKey('update', UserLog::ACTIONS);
        $this->assertArrayHasKey('delete', UserLog::ACTIONS);
        $this->assertArrayHasKey('login', UserLog::ACTIONS);
        $this->assertArrayHasKey('logout', UserLog::ACTIONS);
    }
}
