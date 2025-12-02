<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Workstation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test class untuk user management functionality
 * yang mencakup CRUD operations dan authorization
 *
 * Test cases mencakup positive tests, negative tests, dan authorization tests
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test environment dengan disable Vite
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ==================== INDEX TESTS ====================

    /**
     * Test admin dapat mengakses halaman daftar user
     */
    public function test_admin_can_view_user_index(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->create();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data', 6) // 5 + admin
        );
    }

    /**
     * Test search by NP berfungsi
     */
    public function test_admin_can_search_users_by_np(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->create();
        User::factory()->create(['np' => 'ABC12']);
        User::factory()->create(['np' => 'XYZ99']);

        $response = $this->actingAs($admin)->get('/admin/users?search=ABC');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.search', 'ABC')
        );
    }

    /**
     * Test filter by role berfungsi
     */
    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->create();
        User::factory()->operator()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/users?role=operator');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.role', 'operator')
        );
    }

    /**
     * Test filter by status berfungsi
     */
    public function test_admin_can_filter_users_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->create();
        User::factory()->inactive()->count(2)->create();
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/users?status=inactive');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.status', 'inactive')
        );
    }

    /**
     * Test operator tidak dapat mengakses halaman user
     */
    public function test_operator_cannot_view_user_index(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/admin/users');

        $response->assertStatus(403);
    }

    // ==================== CREATE TESTS ====================

    /**
     * Test admin dapat mengakses halaman create user
     */
    public function test_admin_can_view_create_user_page(): void
    {
        $admin = User::factory()->admin()->create();
        Workstation::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Create')
            ->has('workstations', 3)
            ->has('roles')
        );
    }

    /**
     * Test admin dapat membuat user dengan password default
     */
    public function test_admin_can_create_user_with_default_password(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'np' => 'test1',
            'role' => 'operator',
            'workstation_id' => $workstation->id,
            'use_default' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'np' => 'TEST1',
            'role' => 'operator',
        ]);

        // Verify default password works
        $user = User::where('np', 'TEST1')->first();
        $this->assertTrue(\Hash::check('PeruriTEST1', $user->password));
    }

    /**
     * Test admin dapat membuat user dengan custom password
     */
    public function test_admin_can_create_user_with_custom_password(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'np' => 'test2',
            'password' => 'secret123',
            'role' => 'admin',
            'workstation_id' => $workstation->id,
            'use_default' => false,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/users');

        $user = User::where('np', 'TEST2')->first();
        $this->assertTrue(\Hash::check('secret123', $user->password));
    }

    /**
     * Test NP wajib diisi
     */
    public function test_np_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'np' => '',
            'role' => 'operator',
            'workstation_id' => $workstation->id,
        ]);

        $response->assertSessionHasErrors('np');
    }

    /**
     * Test NP harus unique
     */
    public function test_np_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();
        User::factory()->create(['np' => 'EXIST']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'np' => 'exist',
            'role' => 'operator',
            'workstation_id' => $workstation->id,
            'use_default' => true,
        ]);

        $response->assertSessionHasErrors('np');
    }

    /**
     * Test NP maksimal 5 karakter
     */
    public function test_np_max_length(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'np' => '123456',
            'role' => 'operator',
            'workstation_id' => $workstation->id,
            'use_default' => true,
        ]);

        $response->assertSessionHasErrors('np');
    }

    /**
     * Test workstation wajib dipilih
     */
    public function test_workstation_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'np' => 'test3',
            'role' => 'operator',
            'workstation_id' => '',
            'use_default' => true,
        ]);

        $response->assertSessionHasErrors('workstation_id');
    }

    // ==================== EDIT TESTS ====================

    /**
     * Test admin dapat mengakses halaman edit user
     */
    public function test_admin_can_view_edit_user_page(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        Workstation::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Edit')
            ->has('user')
            ->has('workstations')
            ->has('roles')
        );
    }

    /**
     * Test admin dapat update user
     */
    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->operator()->create(['name' => 'Old Name']);
        $newWorkstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'New Name',
            'role' => 'admin',
            'workstation_id' => $newWorkstation->id,
            'is_active' => false,
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'role' => 'admin',
            'workstation_id' => $newWorkstation->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test admin dapat update password user
     */
    public function test_admin_can_update_user_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $workstation = Workstation::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'password' => 'newpassword',
            'role' => $user->role->value,
            'workstation_id' => $workstation->id,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertTrue(\Hash::check('newpassword', $user->password));
    }

    /**
     * Test password tidak diubah jika kosong
     */
    public function test_password_not_changed_if_empty(): void
    {
        $admin = User::factory()->admin()->create();
        $workstation = Workstation::factory()->create();
        $user = User::factory()->create([
            'password' => 'oldpassword',
            'workstation_id' => $workstation->id,
        ]);
        $oldPassword = $user->password;

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'password' => '',
            'role' => $user->role->value,
            'workstation_id' => $workstation->id,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertEquals($oldPassword, $user->password);
    }

    // ==================== DELETE TESTS ====================

    /**
     * Test admin dapat menghapus user lain
     */
    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test admin tidak dapat menghapus dirinya sendiri
     */
    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
