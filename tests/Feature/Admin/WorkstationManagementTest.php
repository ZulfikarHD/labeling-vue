<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test class untuk workstation management functionality
 * yang mencakup CRUD operations dan toggle status aktif
 *
 * Test cases mencakup positive tests, negative tests, dan authorization tests
 */
class WorkstationManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test environment dengan disable Vite
     * untuk menghindari manifest error saat testing
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ==================== INDEX TESTS ====================

    /**
     * Test admin dapat mengakses halaman daftar workstation
     */
    public function test_admin_can_view_workstation_index(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/workstations');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Workstations/Index')
            ->has('workstations', 3)
        );
    }

    /**
     * Test operator tidak dapat mengakses halaman workstation
     */
    public function test_operator_cannot_view_workstation_index(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/admin/workstations');

        $response->assertStatus(403);
    }

    /**
     * Test guest di-redirect ke login
     */
    public function test_guest_is_redirected_from_workstation_index(): void
    {
        $response = $this->get('/admin/workstations');

        $response->assertRedirect('/login');
    }

    // ==================== CREATE TESTS ====================

    /**
     * Test admin dapat mengakses halaman create workstation
     */
    public function test_admin_can_view_create_workstation_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/workstations/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Workstations/Create')
        );
    }

    /**
     * Test admin dapat membuat workstation baru
     */
    public function test_admin_can_create_workstation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/workstations', [
            'name' => 'Team Baru',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/workstations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('workstations', [
            'name' => 'Team Baru',
            'is_active' => true,
        ]);
    }

    /**
     * Test workstation name wajib diisi
     */
    public function test_workstation_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/workstations', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test workstation name harus unique
     */
    public function test_workstation_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->create(['name' => 'Team 1']);

        $response = $this->actingAs($admin)->post('/admin/workstations', [
            'name' => 'Team 1',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test workstation name maksimal 50 karakter
     */
    public function test_workstation_name_max_length(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/workstations', [
            'name' => str_repeat('a', 51),
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ==================== EDIT TESTS ====================

    /**
     * Test admin dapat mengakses halaman edit workstation
     */
    public function test_admin_can_view_edit_workstation_page(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/workstations/{$workstation->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Workstations/Edit')
            ->has('workstation')
        );
    }

    /**
     * Test admin dapat update workstation
     */
    public function test_admin_can_update_workstation(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create(['name' => 'Team Lama']);

        $response = $this->actingAs($admin)->put("/admin/workstations/{$workstation->id}", [
            'name' => 'Team Baru',
            'is_active' => false,
        ]);

        $response->assertRedirect('/admin/workstations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('workstations', [
            'id' => $workstation->id,
            'name' => 'Team Baru',
            'is_active' => false,
        ]);
    }

    /**
     * Test update workstation dengan nama yang sama (tidak berubah)
     */
    public function test_admin_can_update_workstation_without_changing_name(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create(['name' => 'Team 1']);

        $response = $this->actingAs($admin)->put("/admin/workstations/{$workstation->id}", [
            'name' => 'Team 1',
            'is_active' => false,
        ]);

        $response->assertRedirect('/admin/workstations');
        $response->assertSessionHas('success');
    }

    /**
     * Test update workstation dengan nama yang sudah ada (milik workstation lain)
     */
    public function test_update_workstation_with_existing_name_fails(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->create(['name' => 'Team 1']);
        $workstation = Workstation::factory()->create(['name' => 'Team 2']);

        $response = $this->actingAs($admin)->put("/admin/workstations/{$workstation->id}", [
            'name' => 'Team 1',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ==================== DELETE TESTS ====================

    /**
     * Test admin dapat menghapus workstation tanpa user
     */
    public function test_admin_can_delete_workstation_without_users(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/workstations/{$workstation->id}");

        $response->assertRedirect('/admin/workstations');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('workstations', [
            'id' => $workstation->id,
        ]);
    }

    /**
     * Test admin tidak dapat menghapus workstation yang memiliki user
     */
    public function test_admin_cannot_delete_workstation_with_users(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();
        User::factory()->create(['workstation_id' => $workstation->id]);

        $response = $this->actingAs($admin)->delete("/admin/workstations/{$workstation->id}");

        $response->assertRedirect('/admin/workstations');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('workstations', [
            'id' => $workstation->id,
        ]);
    }

    // ==================== TOGGLE ACTIVE TESTS ====================

    /**
     * Test admin dapat toggle status aktif workstation
     */
    public function test_admin_can_toggle_workstation_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patch("/admin/workstations/{$workstation->id}/toggle-active");

        $response->assertRedirect('/admin/workstations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('workstations', [
            'id' => $workstation->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test toggle workstation dari nonaktif ke aktif
     */
    public function test_admin_can_toggle_inactive_workstation_to_active(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->patch("/admin/workstations/{$workstation->id}/toggle-active");

        $response->assertRedirect('/admin/workstations');

        $this->assertDatabaseHas('workstations', [
            'id' => $workstation->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test operator tidak dapat toggle status workstation
     */
    public function test_operator_cannot_toggle_workstation_status(): void
    {
        $operator = User::factory()->operator()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($operator)->patch("/admin/workstations/{$workstation->id}/toggle-active");

        $response->assertStatus(403);
    }
}
