<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test class untuk logout functionality
 * yang mencakup logout success dan session clearing
 */
class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test dengan disable Vite untuk testing
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable Vite untuk testing tanpa perlu build
        $this->withoutVite();
    }

    /**
     * Test authenticated user dapat logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Test session di-invalidate setelah logout
     */
    public function test_session_is_invalidated_after_logout(): void
    {
        $user = User::factory()->create();

        // Login terlebih dahulu
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Kemudian logout
        $response = $this->post('/logout');

        // Session harus di-regenerate dan user tidak authenticated
        $this->assertGuest();
    }

    /**
     * Test guest tidak dapat akses logout route
     */
    public function test_guest_cannot_access_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }

    /**
     * Test redirect ke login page setelah logout
     */
    public function test_redirects_to_login_after_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
    }

    /**
     * Test user tidak dapat akses protected route setelah logout
     */
    public function test_user_cannot_access_protected_route_after_logout(): void
    {
        $user = User::factory()->create();

        // Login dan akses home
        $this->actingAs($user);
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);

        // Logout
        $this->post('/logout');

        // Coba akses home lagi - harus redirect ke login
        $protectedResponse = $this->get('/');
        $protectedResponse->assertRedirect('/login');
    }
}
