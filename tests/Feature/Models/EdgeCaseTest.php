<?php

namespace Tests\Feature\Models;

use App\Enums\CutSide;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\UserRole;
use App\Models\Label;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Edge Case Tests untuk semua models
 * yang memverifikasi boundary conditions, null values, constraints, dan cascading
 */
class EdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    // ==================== PRODUCTION ORDER EDGE CASES ====================

    /**
     * Test progress 100% ketika semua labels selesai
     */
    public function test_progress_returns_100_when_all_labels_completed(): void
    {
        $order = ProductionOrder::factory()->create();
        Label::factory()->count(5)->completed()->create([
            'production_order_id' => $order->id,
        ]);

        $this->assertEquals(100, $order->progress);
    }

    /**
     * Test progress dengan jumlah label ganjil (edge case rounding)
     */
    public function test_progress_rounds_correctly_with_odd_label_count(): void
    {
        $order = ProductionOrder::factory()->create();

        // 1 of 3 completed = 33.33... should round to 33
        Label::factory()->count(1)->completed()->create([
            'production_order_id' => $order->id,
        ]);
        Label::factory()->count(2)->create([
            'production_order_id' => $order->id,
        ]);

        $this->assertEquals(33, $order->progress);
    }

    /**
     * Test progress dengan 2 of 3 completed = 66.66... should round to 67
     */
    public function test_progress_rounds_up_correctly(): void
    {
        $order = ProductionOrder::factory()->create();

        Label::factory()->count(2)->completed()->create([
            'production_order_id' => $order->id,
        ]);
        Label::factory()->count(1)->create([
            'production_order_id' => $order->id,
        ]);

        $this->assertEquals(67, $order->progress);
    }

    /**
     * Test PO number harus unik - duplicate akan throw exception
     */
    public function test_po_number_must_be_unique(): void
    {
        ProductionOrder::factory()->create(['po_number' => 123456]);

        $this->expectException(QueryException::class);

        ProductionOrder::factory()->create(['po_number' => 123456]);
    }

    /**
     * Test order tanpa team assignment (null team_id)
     */
    public function test_order_can_have_null_team(): void
    {
        $order = ProductionOrder::factory()->create(['team_id' => null]);

        $this->assertNull($order->team);
        $this->assertNull($order->team_id);
    }

    /**
     * Test order dengan inschiet_sheets = 0 (boundary)
     */
    public function test_has_inschiet_false_when_zero_sheets(): void
    {
        $order = ProductionOrder::factory()->create(['inschiet_sheets' => 0]);

        $this->assertFalse($order->has_inschiet);
    }

    /**
     * Test order dengan inschiet_sheets = 1 (minimum positive)
     */
    public function test_has_inschiet_true_when_one_sheet(): void
    {
        $order = ProductionOrder::factory()->create(['inschiet_sheets' => 1]);

        $this->assertTrue($order->has_inschiet);
    }

    /**
     * Test order dengan nilai sheets sangat besar
     */
    public function test_order_handles_large_sheet_values(): void
    {
        $order = ProductionOrder::factory()->create([
            'total_sheets' => 999999,
            'total_rims' => 999,
            'inschiet_sheets' => 999,
        ]);

        $this->assertEquals(999999, $order->total_sheets);
        $this->assertTrue($order->has_inschiet);
    }

    /**
     * Test MMEA order tidak memiliki inschiet (business rule)
     */
    public function test_mmea_order_factory_has_no_inschiet(): void
    {
        $order = ProductionOrder::factory()->mmea()->create();

        $this->assertEquals(0, $order->inschiet_sheets);
        $this->assertFalse($order->has_inschiet);
    }

    /**
     * Test chaining multiple scopes
     */
    public function test_can_chain_multiple_scopes(): void
    {
        $team = Workstation::factory()->create();

        ProductionOrder::factory()->regular()->inProgress()->create(['team_id' => $team->id]);
        ProductionOrder::factory()->regular()->completed()->create(['team_id' => $team->id]);
        ProductionOrder::factory()->mmea()->inProgress()->create(['team_id' => $team->id]);

        $result = ProductionOrder::regular()->inProgress()->forTeam($team->id)->get();

        $this->assertCount(1, $result);
    }

    // ==================== LABEL EDGE CASES ====================

    /**
     * Test composite unique constraint - same order, rim, cut_side should fail
     */
    public function test_label_composite_unique_constraint(): void
    {
        $order = ProductionOrder::factory()->create();

        Label::factory()->create([
            'production_order_id' => $order->id,
            'rim_number' => 1,
            'cut_side' => CutSide::Left,
        ]);

        $this->expectException(QueryException::class);

        Label::factory()->create([
            'production_order_id' => $order->id,
            'rim_number' => 1,
            'cut_side' => CutSide::Left,
        ]);
    }

    /**
     * Test same rim but different cut_side is allowed
     */
    public function test_same_rim_different_cut_side_allowed(): void
    {
        $order = ProductionOrder::factory()->create();

        $leftLabel = Label::factory()->create([
            'production_order_id' => $order->id,
            'rim_number' => 1,
            'cut_side' => CutSide::Left,
        ]);

        $rightLabel = Label::factory()->create([
            'production_order_id' => $order->id,
            'rim_number' => 1,
            'cut_side' => CutSide::Right,
        ]);

        $this->assertDatabaseHas('labels', ['id' => $leftLabel->id]);
        $this->assertDatabaseHas('labels', ['id' => $rightLabel->id]);
    }

    /**
     * Test cascade delete - labels deleted when order deleted
     */
    public function test_labels_cascade_deleted_when_order_deleted(): void
    {
        $order = ProductionOrder::factory()->create();
        $labels = Label::factory()->count(3)->create([
            'production_order_id' => $order->id,
        ]);

        $labelIds = $labels->pluck('id')->toArray();

        $order->delete();

        foreach ($labelIds as $labelId) {
            $this->assertDatabaseMissing('labels', ['id' => $labelId]);
        }
    }

    /**
     * Test label dengan null workstation
     */
    public function test_label_can_have_null_workstation(): void
    {
        $label = Label::factory()->create(['workstation_id' => null]);

        $this->assertNull($label->workstation);
    }

    /**
     * Test label rim number 999 adalah inschiet
     */
    public function test_rim_999_is_inschiet(): void
    {
        $label = Label::factory()->inschiet()->create();

        $this->assertEquals(999, $label->rim_number);
        $this->assertTrue($label->is_inschiet);
    }

    /**
     * Test label dengan rim number boundary (1 - minimum)
     */
    public function test_label_minimum_rim_number(): void
    {
        $label = Label::factory()->create(['rim_number' => 1]);

        $this->assertEquals(1, $label->rim_number);
    }

    /**
     * Test MMEA label tanpa cut_side (null)
     */
    public function test_mmea_label_has_null_cut_side(): void
    {
        $label = Label::factory()->mmea()->create();

        $this->assertNull($label->cut_side);
    }

    /**
     * Test label state transitions: pending -> in_progress -> completed
     */
    public function test_label_state_transitions(): void
    {
        $label = Label::factory()->create([
            'inspector_np' => null,
            'started_at' => null,
            'finished_at' => null,
        ]);

        // Initial state - pending
        $this->assertFalse($label->is_in_progress);
        $this->assertFalse($label->is_completed);

        // Start inspection
        $label->startInspection('12345');
        $label->refresh();

        $this->assertTrue($label->is_in_progress);
        $this->assertFalse($label->is_completed);

        // Finish inspection
        $label->finishInspection();
        $label->refresh();

        $this->assertFalse($label->is_in_progress);
        $this->assertTrue($label->is_completed);
    }

    /**
     * Test multiple labels with same order but different rims
     */
    public function test_multiple_labels_same_order_different_rims(): void
    {
        $order = ProductionOrder::factory()->create();

        for ($rim = 1; $rim <= 10; $rim++) {
            Label::factory()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Left,
            ]);
        }

        $this->assertCount(10, $order->labels);
    }

    // ==================== USER EDGE CASES ====================

    /**
     * Test user tanpa workstation assignment
     */
    public function test_user_can_have_null_workstation(): void
    {
        $user = User::factory()->create(['workstation_id' => null]);

        $this->assertNull($user->workstation);
    }

    /**
     * Test user dengan NP 5 digit (max length)
     */
    public function test_user_np_max_length(): void
    {
        $user = User::factory()->create(['np' => '99999']);

        $this->assertEquals('99999', $user->np);
    }

    /**
     * Test user dengan NP 1 digit (min length)
     */
    public function test_user_np_min_length(): void
    {
        $user = User::factory()->create(['np' => '1']);

        $this->assertEquals('1', $user->np);
    }

    /**
     * Test multiple scopes chaining untuk User
     */
    public function test_user_multiple_scopes_chaining(): void
    {
        User::factory()->admin()->create(['is_active' => true]);
        User::factory()->admin()->create(['is_active' => false]);
        User::factory()->operator()->create(['is_active' => true]);

        $activeAdmins = User::active()->admins()->get();

        $this->assertCount(1, $activeAdmins);
    }

    /**
     * Test user inspected labels relationship dengan NP
     */
    public function test_user_inspected_labels_uses_np_not_id(): void
    {
        $user = User::factory()->create(['np' => '12345']);
        $order = ProductionOrder::factory()->create();

        Label::factory()->create([
            'production_order_id' => $order->id,
            'inspector_np' => '12345',
        ]);
        Label::factory()->create([
            'production_order_id' => $order->id,
            'inspector_np' => '99999', // Different NP
        ]);

        $this->assertCount(1, $user->inspectedLabels);
    }

    // ==================== WORKSTATION EDGE CASES ====================

    /**
     * Test workstation tanpa users
     */
    public function test_workstation_with_no_users(): void
    {
        $workstation = Workstation::factory()->create();

        $this->assertCount(0, $workstation->users);
        $this->assertTrue($workstation->users->isEmpty());
    }

    /**
     * Test workstation tanpa orders
     */
    public function test_workstation_with_no_orders(): void
    {
        $workstation = Workstation::factory()->create();

        $this->assertCount(0, $workstation->productionOrders);
        $this->assertTrue($workstation->productionOrders->isEmpty());
    }

    /**
     * Test user workstation_id menjadi null ketika workstation dihapus
     */
    public function test_user_workstation_null_on_delete(): void
    {
        $workstation = Workstation::factory()->create();
        $user = User::factory()->create(['workstation_id' => $workstation->id]);

        $workstation->delete();

        $user->refresh();
        $this->assertNull($user->workstation_id);
    }

    /**
     * Test order team_id menjadi null ketika workstation dihapus
     */
    public function test_order_team_null_on_workstation_delete(): void
    {
        $workstation = Workstation::factory()->create();
        $order = ProductionOrder::factory()->create(['team_id' => $workstation->id]);

        $workstation->delete();

        $order->refresh();
        $this->assertNull($order->team_id);
    }

    // ==================== ENUM EDGE CASES ====================

    /**
     * Test OrderType enum helper methods
     */
    public function test_order_type_enum_helpers(): void
    {
        $this->assertEquals(2, OrderType::Regular->labelsPerRim());
        $this->assertEquals(1, OrderType::Mmea->labelsPerRim());

        $this->assertTrue(OrderType::Regular->requiresCutSide());
        $this->assertFalse(OrderType::Mmea->requiresCutSide());
    }

    /**
     * Test OrderStatus enum progression
     */
    public function test_order_status_enum_progression(): void
    {
        $this->assertEquals(OrderStatus::InProgress, OrderStatus::Registered->nextStatus());
        $this->assertEquals(OrderStatus::Completed, OrderStatus::InProgress->nextStatus());
        $this->assertNull(OrderStatus::Completed->nextStatus());
    }

    /**
     * Test CutSide enum opposite
     */
    public function test_cut_side_enum_opposite(): void
    {
        $this->assertEquals(CutSide::Right, CutSide::Left->opposite());
        $this->assertEquals(CutSide::Left, CutSide::Right->opposite());
    }

    /**
     * Test CutSide priority (left before right)
     */
    public function test_cut_side_priority(): void
    {
        $this->assertLessThan(CutSide::Right->priority(), CutSide::Left->priority());
    }

    /**
     * Test UserRole isAdmin helper
     */
    public function test_user_role_is_admin_helper(): void
    {
        $this->assertTrue(UserRole::Admin->isAdmin());
        $this->assertFalse(UserRole::Operator->isAdmin());
    }

    // ==================== BOUNDARY & STRESS TESTS ====================

    /**
     * Test creating many labels for single order (with unique rim/side combinations)
     */
    public function test_order_with_many_labels(): void
    {
        $order = ProductionOrder::factory()->create();

        // Create 50 rims with both left and right = 100 labels
        for ($rim = 1; $rim <= 50; $rim++) {
            Label::factory()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Left,
            ]);
            Label::factory()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Right,
            ]);
        }

        $this->assertCount(100, $order->labels);
    }

    /**
     * Test progress calculation dengan banyak labels
     */
    public function test_progress_with_many_labels(): void
    {
        $order = ProductionOrder::factory()->create();

        // Create 75 completed labels (rims 1-37 left+right, rim 38 left)
        for ($rim = 1; $rim <= 37; $rim++) {
            Label::factory()->completed()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Left,
            ]);
            Label::factory()->completed()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Right,
            ]);
        }
        Label::factory()->completed()->create([
            'production_order_id' => $order->id,
            'rim_number' => 38,
            'cut_side' => CutSide::Left,
        ]);

        // Create 25 pending labels (rim 38 right, rims 39-50 left+right)
        Label::factory()->create([
            'production_order_id' => $order->id,
            'rim_number' => 38,
            'cut_side' => CutSide::Right,
        ]);
        for ($rim = 39; $rim <= 50; $rim++) {
            Label::factory()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Left,
            ]);
            Label::factory()->create([
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => CutSide::Right,
            ]);
        }

        $this->assertEquals(75, $order->progress);
    }

    /**
     * Test empty scopes return empty collections
     */
    public function test_scopes_return_empty_when_no_matches(): void
    {
        // No data created
        $this->assertCount(0, ProductionOrder::regular()->get());
        $this->assertCount(0, ProductionOrder::mmea()->get());
        $this->assertCount(0, Label::pending()->get());
        $this->assertCount(0, User::admins()->get());
        $this->assertCount(0, Workstation::active()->get());
    }

    /**
     * Test fillable attributes are mass assignable
     */
    public function test_production_order_fillable_attributes(): void
    {
        $data = [
            'po_number' => 999888,
            'obc_number' => 'OBC-TEST',
            'order_type' => OrderType::Regular,
            'product_type' => 'test product',
            'total_sheets' => 5000,
            'total_rims' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'inschiet_sheets' => 0,
            'status' => OrderStatus::Registered,
        ];

        $order = ProductionOrder::create($data);

        $this->assertEquals(999888, $order->po_number);
        $this->assertEquals('OBC-TEST', $order->obc_number);
    }

    /**
     * Test label fillable attributes
     */
    public function test_label_fillable_attributes(): void
    {
        $order = ProductionOrder::factory()->create();

        $data = [
            'production_order_id' => $order->id,
            'rim_number' => 999,
            'cut_side' => CutSide::Left,
            'is_inschiet' => true,
            'inspector_np' => '12345',
            'pack_sheets' => 100,
        ];

        $label = Label::create($data);

        $this->assertEquals(999, $label->rim_number);
        $this->assertTrue($label->is_inschiet);
        $this->assertEquals('12345', $label->inspector_np);
    }
}
