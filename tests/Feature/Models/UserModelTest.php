<?php

namespace Tests\Feature\Models;

use App\Enums\UserRole;
use App\Models\Label;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk User model
 * yang memverifikasi relationships, scopes, accessors, dan factory
 */
class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa user dapat dibuat menggunakan factory
     */
    public function test_can_create_user_using_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'np' => $user->np,
        ]);
    }

    /**
     * Test bahwa user memiliki relasi belongsTo ke workstation
     */
    public function test_user_belongs_to_workstation(): void
    {
        $workstation = Workstation::factory()->create();
        $user = User::factory()->create([
            'workstation_id' => $workstation->id,
        ]);

        $this->assertInstanceOf(Workstation::class, $user->workstation);
        $this->assertEquals($workstation->id, $user->workstation->id);
    }

    /**
     * Test bahwa user memiliki relasi hasMany ke inspected labels
     */
    public function test_user_has_many_inspected_labels(): void
    {
        $user = User::factory()->create();
        $order = ProductionOrder::factory()->create();
        Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
            'inspector_np' => $user->np,
        ]);

        $this->assertCount(2, $user->inspectedLabels);
        $this->assertInstanceOf(Label::class, $user->inspectedLabels->first());
    }

    /**
     * Test scope active hanya mengembalikan user aktif
     */
    public function test_scope_active_returns_only_active_users(): void
    {
        User::factory()->count(3)->create(['is_active' => true]);
        User::factory()->count(2)->create(['is_active' => false]);

        $activeUsers = User::active()->get();

        $this->assertCount(3, $activeUsers);
    }

    /**
     * Test scope admins hanya mengembalikan user admin
     */
    public function test_scope_admins_returns_only_admin_users(): void
    {
        User::factory()->count(2)->admin()->create();
        User::factory()->count(3)->operator()->create();

        $admins = User::admins()->get();

        $this->assertCount(2, $admins);
    }

    /**
     * Test scope operators hanya mengembalikan user operator
     */
    public function test_scope_operators_returns_only_operator_users(): void
    {
        User::factory()->count(2)->admin()->create();
        User::factory()->count(3)->operator()->create();

        $operators = User::operators()->get();

        $this->assertCount(3, $operators);
    }

    /**
     * Test method isAdmin mengembalikan true untuk admin
     */
    public function test_is_admin_returns_true_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOperator());
    }

    /**
     * Test method isOperator mengembalikan true untuk operator
     */
    public function test_is_operator_returns_true_for_operator(): void
    {
        $operator = User::factory()->operator()->create();

        $this->assertTrue($operator->isOperator());
        $this->assertFalse($operator->isAdmin());
    }

    /**
     * Test role di-cast ke UserRole enum
     */
    public function test_role_cast_to_enum(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::Admin, $user->role);
    }

    /**
     * Test factory state inactive
     */
    public function test_factory_inactive_state(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->is_active);
    }

    /**
     * Test NP harus unik
     */
    public function test_np_must_be_unique(): void
    {
        User::factory()->create(['np' => '12345']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['np' => '12345']);
    }
}
