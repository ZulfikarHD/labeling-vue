<?php

namespace Tests\Feature\Models;

use App\Models\Label;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Workstation model
 * yang memverifikasi relationships, scopes, dan factory
 */
class WorkstationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa workstation dapat dibuat menggunakan factory
     */
    public function test_can_create_workstation_using_factory(): void
    {
        $workstation = Workstation::factory()->create();

        $this->assertDatabaseHas('workstations', [
            'id' => $workstation->id,
            'name' => $workstation->name,
        ]);
    }

    /**
     * Test bahwa workstation memiliki relasi hasMany ke users
     */
    public function test_workstation_has_many_users(): void
    {
        $workstation = Workstation::factory()->create();
        $users = User::factory()->count(3)->create([
            'workstation_id' => $workstation->id,
        ]);

        $this->assertCount(3, $workstation->users);
        $this->assertInstanceOf(User::class, $workstation->users->first());
    }

    /**
     * Test bahwa workstation memiliki relasi hasMany ke production orders
     */
    public function test_workstation_has_many_production_orders(): void
    {
        $workstation = Workstation::factory()->create();
        $orders = ProductionOrder::factory()->count(2)->create([
            'team_id' => $workstation->id,
        ]);

        $this->assertCount(2, $workstation->productionOrders);
        $this->assertInstanceOf(ProductionOrder::class, $workstation->productionOrders->first());
    }

    /**
     * Test bahwa workstation memiliki relasi hasMany ke labels
     */
    public function test_workstation_has_many_labels(): void
    {
        $workstation = Workstation::factory()->create();
        $order = ProductionOrder::factory()->create();
        $labels = Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
            'workstation_id' => $workstation->id,
        ]);

        $this->assertCount(2, $workstation->labels);
        $this->assertInstanceOf(Label::class, $workstation->labels->first());
    }

    /**
     * Test scope active hanya mengembalikan workstation aktif
     */
    public function test_scope_active_returns_only_active_workstations(): void
    {
        Workstation::factory()->count(3)->create(['is_active' => true]);
        Workstation::factory()->count(2)->create(['is_active' => false]);

        $activeWorkstations = Workstation::active()->get();

        $this->assertCount(3, $activeWorkstations);
    }

    /**
     * Test factory state inactive
     */
    public function test_factory_inactive_state(): void
    {
        $workstation = Workstation::factory()->inactive()->create();

        $this->assertFalse($workstation->is_active);
    }

    /**
     * Test is_active di-cast ke boolean
     */
    public function test_is_active_cast_to_boolean(): void
    {
        $workstation = Workstation::factory()->create(['is_active' => true]);

        $this->assertIsBool($workstation->is_active);
        $this->assertTrue($workstation->is_active);
    }
}
