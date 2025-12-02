<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Test class untuk auth middleware functionality
 * yang mencakup route protection dan admin access
 */
class AuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test routes untuk middleware testing
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable Vite untuk testing tanpa perlu build
        $this->withoutVite();

        // Definisikan test routes untuk admin middleware
        Route::middleware(['auth', 'admin'])->group(function (): void {
            Route::get('/admin-test', fn () => response('Admin access granted'))
                ->name('admin.test');
        });
    }

    /**
     * Test guest redirect ke login saat akses protected route
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user dapat akses protected route
     */
    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test admin dapat akses admin-only route
     */
    public function test_admin_can_access_admin_route(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin-test');

        $response->assertStatus(200);
        $response->assertSee('Admin access granted');
    }

    /**
     * Test operator tidak dapat akses admin-only route
     */
    public function test_operator_cannot_access_admin_route(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/admin-test');

        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat akses admin-only route
     */
    public function test_guest_cannot_access_admin_route(): void
    {
        $response = $this->get('/admin-test');

        $response->assertRedirect('/login');
    }

    /**
     * Test inactive user tidak dapat akses protected route setelah login
     */
    public function test_inactive_user_cannot_login_to_access_protected_route(): void
    {
        $user = User::factory()->inactive()->create([
            'np' => '12345',
        ]);

        // Attempt login
        $loginResponse = $this->post('/login', [
            'np' => '12345',
            'password' => 'password',
        ]);

        // Should not be authenticated
        $this->assertGuest();

        // Try to access protected route
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin middleware returns 403 dengan message
     */
    public function test_admin_middleware_returns_forbidden_status(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/admin-test');

        $response->assertStatus(403);
    }

    /**
     * Test auth middleware preserves intended URL
     */
    public function test_auth_middleware_preserves_intended_url(): void
    {
        // Guest mencoba akses protected route
        $this->get('/');

        // Login
        $user = User::factory()->create([
            'np' => '12345',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'np' => '12345',
            'password' => 'password',
        ]);

        // Seharusnya redirect ke intended URL (home)
        $response->assertRedirect('/');
    }
}
