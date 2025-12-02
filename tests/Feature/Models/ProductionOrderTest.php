<?php

namespace Tests\Feature\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Label;
use App\Models\ProductionOrder;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk ProductionOrder model
 * yang memverifikasi relationships, scopes, accessors, dan factory
 */
class ProductionOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa production order dapat dibuat menggunakan factory
     */
    public function test_can_create_production_order_using_factory(): void
    {
        $order = ProductionOrder::factory()->create();

        $this->assertDatabaseHas('production_orders', [
            'id' => $order->id,
            'po_number' => $order->po_number,
        ]);
    }

    /**
     * Test bahwa production order memiliki relasi belongsTo ke team (workstation)
     */
    public function test_production_order_belongs_to_team(): void
    {
        $workstation = Workstation::factory()->create();
        $order = ProductionOrder::factory()->create([
            'team_id' => $workstation->id,
        ]);

        $this->assertInstanceOf(Workstation::class, $order->team);
        $this->assertEquals($workstation->id, $order->team->id);
    }

    /**
     * Test bahwa production order memiliki relasi hasMany ke labels
     */
    public function test_production_order_has_many_labels(): void
    {
        $order = ProductionOrder::factory()->create();
        Label::factory()->count(3)->create([
            'production_order_id' => $order->id,
        ]);

        $this->assertCount(3, $order->labels);
        $this->assertInstanceOf(Label::class, $order->labels->first());
    }

    /**
     * Test scope regular hanya mengembalikan order regular
     */
    public function test_scope_regular_returns_only_regular_orders(): void
    {
        ProductionOrder::factory()->count(3)->regular()->create();
        ProductionOrder::factory()->count(2)->mmea()->create();

        $regularOrders = ProductionOrder::regular()->get();

        $this->assertCount(3, $regularOrders);
    }

    /**
     * Test scope mmea hanya mengembalikan order MMEA
     */
    public function test_scope_mmea_returns_only_mmea_orders(): void
    {
        ProductionOrder::factory()->count(3)->regular()->create();
        ProductionOrder::factory()->count(2)->mmea()->create();

        $mmeaOrders = ProductionOrder::mmea()->get();

        $this->assertCount(2, $mmeaOrders);
    }

    /**
     * Test scope registered hanya mengembalikan order dengan status registered
     */
    public function test_scope_registered_returns_only_registered_orders(): void
    {
        ProductionOrder::factory()->count(2)->create(['status' => OrderStatus::Registered]);
        ProductionOrder::factory()->count(1)->inProgress()->create();
        ProductionOrder::factory()->count(1)->completed()->create();

        $registeredOrders = ProductionOrder::registered()->get();

        $this->assertCount(2, $registeredOrders);
    }

    /**
     * Test scope inProgress hanya mengembalikan order dengan status in_progress
     */
    public function test_scope_in_progress_returns_only_in_progress_orders(): void
    {
        ProductionOrder::factory()->count(2)->create(['status' => OrderStatus::Registered]);
        ProductionOrder::factory()->count(1)->inProgress()->create();
        ProductionOrder::factory()->count(1)->completed()->create();

        $inProgressOrders = ProductionOrder::inProgress()->get();

        $this->assertCount(1, $inProgressOrders);
    }

    /**
     * Test scope completed hanya mengembalikan order dengan status completed
     */
    public function test_scope_completed_returns_only_completed_orders(): void
    {
        ProductionOrder::factory()->count(2)->create(['status' => OrderStatus::Registered]);
        ProductionOrder::factory()->count(1)->completed()->create();

        $completedOrders = ProductionOrder::completed()->get();

        $this->assertCount(1, $completedOrders);
    }

    /**
     * Test scope forTeam mengembalikan order untuk team tertentu
     */
    public function test_scope_for_team_returns_orders_for_specific_team(): void
    {
        $team1 = Workstation::factory()->create();
        $team2 = Workstation::factory()->create();

        ProductionOrder::factory()->count(2)->create(['team_id' => $team1->id]);
        ProductionOrder::factory()->count(3)->create(['team_id' => $team2->id]);

        $team1Orders = ProductionOrder::forTeam($team1->id)->get();

        $this->assertCount(2, $team1Orders);
    }

    /**
     * Test accessor hasInschiet mengembalikan true jika ada inschiet
     */
    public function test_has_inschiet_accessor_returns_true_when_inschiet_exists(): void
    {
        $orderWithInschiet = ProductionOrder::factory()->create([
            'inschiet_sheets' => 500,
        ]);
        $orderWithoutInschiet = ProductionOrder::factory()->create([
            'inschiet_sheets' => 0,
        ]);

        $this->assertTrue($orderWithInschiet->has_inschiet);
        $this->assertFalse($orderWithoutInschiet->has_inschiet);
    }

    /**
     * Test accessor progress menghitung persentase label selesai
     */
    public function test_progress_accessor_calculates_completion_percentage(): void
    {
        $order = ProductionOrder::factory()->create();

        // Create 4 labels, 2 completed
        Label::factory()->count(2)->completed()->create([
            'production_order_id' => $order->id,
        ]);
        Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
        ]);

        $this->assertEquals(50, $order->progress);
    }

    /**
     * Test progress accessor mengembalikan 0 jika tidak ada label
     */
    public function test_progress_accessor_returns_zero_when_no_labels(): void
    {
        $order = ProductionOrder::factory()->create();

        $this->assertEquals(0, $order->progress);
    }

    /**
     * Test order_type di-cast ke OrderType enum
     */
    public function test_order_type_cast_to_enum(): void
    {
        $order = ProductionOrder::factory()->regular()->create();

        $this->assertInstanceOf(OrderType::class, $order->order_type);
        $this->assertEquals(OrderType::Regular, $order->order_type);
    }

    /**
     * Test status di-cast ke OrderStatus enum
     */
    public function test_status_cast_to_enum(): void
    {
        $order = ProductionOrder::factory()->inProgress()->create();

        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertEquals(OrderStatus::InProgress, $order->status);
    }

    /**
     * Test helper method isRegular
     */
    public function test_is_regular_method(): void
    {
        $regular = ProductionOrder::factory()->regular()->create();
        $mmea = ProductionOrder::factory()->mmea()->create();

        $this->assertTrue($regular->isRegular());
        $this->assertFalse($mmea->isRegular());
    }

    /**
     * Test helper method isMmea
     */
    public function test_is_mmea_method(): void
    {
        $regular = ProductionOrder::factory()->regular()->create();
        $mmea = ProductionOrder::factory()->mmea()->create();

        $this->assertFalse($regular->isMmea());
        $this->assertTrue($mmea->isMmea());
    }

    /**
     * Test helper method isCompleted
     */
    public function test_is_completed_method(): void
    {
        $completed = ProductionOrder::factory()->completed()->create();
        $inProgress = ProductionOrder::factory()->inProgress()->create();

        $this->assertTrue($completed->isCompleted());
        $this->assertFalse($inProgress->isCompleted());
    }
}
