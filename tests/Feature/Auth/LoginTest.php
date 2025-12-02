<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test class untuk login functionality
 * yang mencakup login success, invalid credentials, dan inactive user
 */
class LoginTest extends TestCase
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
     * Test login page dapat diakses oleh guest
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test user dapat login dengan credentials yang valid
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'np' => '12345',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'np' => '12345',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * Test NP di-convert ke uppercase saat login
     */
    public function test_np_is_converted_to_uppercase(): void
    {
        $user = User::factory()->create([
            'np' => 'ABCDE',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'np' => 'abcde', // lowercase input
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * Test user tidak dapat login dengan password salah
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'np' => '12345',
        ]);

        $response = $this->post('/login', [
            'np' => '12345',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('np');
    }

    /**
     * Test user tidak dapat login dengan NP yang tidak terdaftar
     */
    public function test_user_cannot_login_with_nonexistent_np(): void
    {
        $response = $this->post('/login', [
            'np' => '99999',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('np');
    }

    /**
     * Test user tidak aktif tidak dapat login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'np' => '12345',
        ]);

        $response = $this->post('/login', [
            'np' => '12345',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('np');
    }

    /**
     * Test error message untuk invalid credentials
     */
    public function test_displays_error_message_for_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'np' => '99999',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors([
            'np' => 'NP atau password salah',
        ]);
    }

    /**
     * Test error message untuk inactive user
     */
    public function test_displays_error_message_for_inactive_user(): void
    {
        $user = User::factory()->inactive()->create([
            'np' => '12345',
        ]);

        $response = $this->post('/login', [
            'np' => '12345',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'np' => 'Akun tidak aktif. Hubungi administrator',
        ]);
    }

    /**
     * Test validation error untuk NP kosong
     */
    public function test_np_is_required(): void
    {
        $response = $this->post('/login', [
            'np' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('np');
    }

    /**
     * Test validation error untuk password kosong
     */
    public function test_password_is_required(): void
    {
        $response = $this->post('/login', [
            'np' => '12345',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test authenticated user redirect dari login page
     */
    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    /**
     * Test remember me functionality
     */
    public function test_user_can_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'np' => '12345',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'np' => '12345',
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }
}
