<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Lastminutes\Models\Lastminute;
use Tests\TestCase;

class LastminuteModuleTest extends TestCase
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

    public function test_lastminute_can_be_created()
    {
        $lastminuteData = [
            'title' => 'Test Lastminute Title',
            'redirect' => 'https://example.com',
            'end_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ];

        $lastminute = Lastminute::create($lastminuteData);

        $this->assertInstanceOf(Lastminute::class, $lastminute);
        $this->assertEquals('Test Lastminute Title', $lastminute->title);
        $this->assertEquals('active', $lastminute->status);
        $this->assertEquals(1, $lastminute->weight);
        $this->assertDatabaseHas('lastminutes', ['title' => 'Test Lastminute Title']);
    }

    public function test_lastminute_can_be_updated()
    {
        $lastminute = Lastminute::create([
            'title' => 'Original Title',
            'redirect' => 'https://original.com',
            'status' => 'inactive',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        $lastminute->update([
            'title' => 'Updated Title',
            'status' => 'active',
        ]);

        $this->assertEquals('Updated Title', $lastminute->fresh()->title);
        $this->assertEquals('active', $lastminute->fresh()->status);
    }

    public function test_lastminute_can_be_deleted()
    {
        $lastminute = Lastminute::create([
            'title' => 'Lastminute to Delete',
            'redirect' => 'https://delete.com',
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);
        $lastminuteId = $lastminute->lastminute_id;

        $lastminute->delete();

        $this->assertSoftDeleted('lastminutes', ['lastminute_id' => $lastminuteId]);
    }

    public function test_lastminute_has_required_attributes()
    {
        $lastminute = Lastminute::create([
            'title' => 'Required Attributes Lastminute',
            'redirect' => 'https://required.com',
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($lastminute->lastminute_id);
        $this->assertNotNull($lastminute->title);
        $this->assertNotNull($lastminute->redirect);
        $this->assertNotNull($lastminute->status);
        $this->assertNotNull($lastminute->weight);
    }

    public function test_lastminute_can_have_end_date()
    {
        $endDate = now()->addDays(7);
        $lastminute = Lastminute::create([
            'title' => 'Lastminute with End Date',
            'redirect' => 'https://enddate.com',
            'status' => 'active',
            'weight' => 1,
            'end_at' => $endDate,
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($lastminute->end_at);
        $endAtFormatted = is_string($lastminute->end_at)
            ? Carbon::parse($lastminute->end_at)->format('Y-m-d H:i:s')
            : $lastminute->end_at->format('Y-m-d H:i:s');
        $this->assertEquals($endDate->format('Y-m-d H:i:s'), $endAtFormatted);
    }

    public function test_lastminute_has_weight_for_ordering()
    {
        $lastminute = Lastminute::create([
            'title' => 'Weighted Lastminute',
            'redirect' => 'https://weighted.com',
            'status' => 'active',
            'weight' => 5,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(5, $lastminute->weight);
    }

    public function test_lastminute_scope_active()
    {
        Lastminute::create([
            'title' => 'Active Lastminute',
            'redirect' => 'https://active.com',
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        Lastminute::create([
            'title' => 'Inactive Lastminute',
            'redirect' => 'https://inactive.com',
            'status' => 'inactive',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        $activeCount = Lastminute::active()->count();
        $this->assertEquals(1, $activeCount);
    }

    public function test_lastminute_scope_inactive()
    {
        Lastminute::create([
            'title' => 'Active Lastminute',
            'redirect' => 'https://active.com',
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        Lastminute::create([
            'title' => 'Inactive Lastminute',
            'redirect' => 'https://inactive.com',
            'status' => 'inactive',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        $inactiveCount = Lastminute::inactive()->count();
        $this->assertEquals(1, $inactiveCount);
    }

    public function test_lastminute_scope_expired()
    {
        Lastminute::create([
            'title' => 'Expired Lastminute',
            'redirect' => 'https://expired.com',
            'status' => 'expired',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        Lastminute::create([
            'title' => 'Active Lastminute',
            'redirect' => 'https://active.com',
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        $expiredCount = Lastminute::expired()->count();
        $this->assertEquals(1, $expiredCount);
    }

    public function test_lastminute_belongs_to_creator()
    {
        $lastminute = Lastminute::create([
            'title' => 'Lastminute with Creator',
            'redirect' => 'https://creator.com',
            'status' => 'active',
            'weight' => 1,
            'created_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $lastminute->creator);
        $this->assertEquals($this->user->id, $lastminute->creator->id);
    }
}
