<?php

namespace Tests\Feature\Models;

use App\Enums\CutSide;
use App\Models\Label;
use App\Models\ProductionOrder;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Label model
 * yang memverifikasi relationships, scopes, accessors, dan factory
 */
class LabelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa label dapat dibuat menggunakan factory
     */
    public function test_can_create_label_using_factory(): void
    {
        $label = Label::factory()->create();

        $this->assertDatabaseHas('labels', [
            'id' => $label->id,
            'rim_number' => $label->rim_number,
        ]);
    }

    /**
     * Test bahwa label memiliki relasi belongsTo ke production order
     */
    public function test_label_belongs_to_production_order(): void
    {
        $order = ProductionOrder::factory()->create();
        $label = Label::factory()->create([
            'production_order_id' => $order->id,
        ]);

        $this->assertInstanceOf(ProductionOrder::class, $label->order);
        $this->assertEquals($order->id, $label->order->id);
    }

    /**
     * Test bahwa label memiliki relasi belongsTo ke workstation
     */
    public function test_label_belongs_to_workstation(): void
    {
        $workstation = Workstation::factory()->create();
        $label = Label::factory()->create([
            'workstation_id' => $workstation->id,
        ]);

        $this->assertInstanceOf(Workstation::class, $label->workstation);
        $this->assertEquals($workstation->id, $label->workstation->id);
    }

    /**
     * Test scope pending hanya mengembalikan label tanpa inspector
     */
    public function test_scope_pending_returns_labels_without_inspector(): void
    {
        $order = ProductionOrder::factory()->create();
        Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
            'inspector_np' => null,
        ]);
        Label::factory()->count(1)->inProgress()->create([
            'production_order_id' => $order->id,
        ]);

        $pendingLabels = Label::pending()->get();

        $this->assertCount(2, $pendingLabels);
    }

    /**
     * Test scope processed hanya mengembalikan label dengan inspector
     */
    public function test_scope_processed_returns_labels_with_inspector(): void
    {
        $order = ProductionOrder::factory()->create();
        Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
            'inspector_np' => null,
        ]);
        Label::factory()->count(3)->inProgress()->create([
            'production_order_id' => $order->id,
        ]);

        $processedLabels = Label::processed()->get();

        $this->assertCount(3, $processedLabels);
    }

    /**
     * Test scope inschiet hanya mengembalikan label inschiet
     */
    public function test_scope_inschiet_returns_only_inschiet_labels(): void
    {
        $order = ProductionOrder::factory()->create();
        Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
            'is_inschiet' => false,
        ]);
        Label::factory()->count(1)->inschiet()->create([
            'production_order_id' => $order->id,
        ]);

        $inschietLabels = Label::inschiet()->get();

        $this->assertCount(1, $inschietLabels);
    }

    /**
     * Test scope forOrder mengembalikan label untuk order tertentu
     */
    public function test_scope_for_order_returns_labels_for_specific_order(): void
    {
        $order1 = ProductionOrder::factory()->create();
        $order2 = ProductionOrder::factory()->create();

        Label::factory()->count(2)->create(['production_order_id' => $order1->id]);
        Label::factory()->count(3)->create(['production_order_id' => $order2->id]);

        $order1Labels = Label::forOrder($order1->id)->get();

        $this->assertCount(2, $order1Labels);
    }

    /**
     * Test accessor isCompleted mengembalikan true jika finished_at tidak null
     */
    public function test_is_completed_accessor(): void
    {
        $completedLabel = Label::factory()->completed()->create();
        $pendingLabel = Label::factory()->create();

        $this->assertTrue($completedLabel->is_completed);
        $this->assertFalse($pendingLabel->is_completed);
    }

    /**
     * Test accessor isInProgress mengembalikan true jika started_at ada tapi finished_at null
     */
    public function test_is_in_progress_accessor(): void
    {
        $inProgressLabel = Label::factory()->inProgress()->create();
        $completedLabel = Label::factory()->completed()->create();
        $pendingLabel = Label::factory()->create();

        $this->assertTrue($inProgressLabel->is_in_progress);
        $this->assertFalse($completedLabel->is_in_progress);
        $this->assertFalse($pendingLabel->is_in_progress);
    }

    /**
     * Test cut_side di-cast ke CutSide enum
     */
    public function test_cut_side_cast_to_enum(): void
    {
        $label = Label::factory()->left()->create();

        $this->assertInstanceOf(CutSide::class, $label->cut_side);
        $this->assertEquals(CutSide::Left, $label->cut_side);
    }

    /**
     * Test factory state left
     */
    public function test_factory_left_state(): void
    {
        $label = Label::factory()->left()->create();

        $this->assertEquals(CutSide::Left, $label->cut_side);
    }

    /**
     * Test factory state right
     */
    public function test_factory_right_state(): void
    {
        $label = Label::factory()->right()->create();

        $this->assertEquals(CutSide::Right, $label->cut_side);
    }

    /**
     * Test factory state inschiet
     */
    public function test_factory_inschiet_state(): void
    {
        $label = Label::factory()->inschiet()->create();

        $this->assertEquals(999, $label->rim_number);
        $this->assertTrue($label->is_inschiet);
    }

    /**
     * Test factory state mmea (tanpa cut side)
     */
    public function test_factory_mmea_state(): void
    {
        $label = Label::factory()->mmea()->create();

        $this->assertNull($label->cut_side);
    }

    /**
     * Test method startInspection
     */
    public function test_start_inspection_method(): void
    {
        $label = Label::factory()->create([
            'inspector_np' => null,
            'started_at' => null,
        ]);

        $label->startInspection('12345');

        $label->refresh();
        $this->assertEquals('12345', $label->inspector_np);
        $this->assertNotNull($label->started_at);
    }

    /**
     * Test method finishInspection
     */
    public function test_finish_inspection_method(): void
    {
        $label = Label::factory()->inProgress()->create();

        $label->finishInspection('54321');

        $label->refresh();
        $this->assertNotNull($label->finished_at);
        $this->assertEquals('54321', $label->inspector_2_np);
    }

    /**
     * Test method finishInspection tanpa inspector kedua
     */
    public function test_finish_inspection_without_second_inspector(): void
    {
        $label = Label::factory()->inProgress()->create();

        $label->finishInspection();

        $label->refresh();
        $this->assertNotNull($label->finished_at);
        $this->assertNull($label->inspector_2_np);
    }
}
