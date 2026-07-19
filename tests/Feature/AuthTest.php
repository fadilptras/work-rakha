<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_is_redirected_to_user_dashboard()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/users/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_and_is_redirected_to_admin_dashboard()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_normal_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');

        // Middleware redirect.if.admin will redirect normal user to /users/dashboard
        $response->assertRedirect('/users/dashboard');
    }

    public function test_admin_idle_timeout_logs_out_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Akses pertama kali mengeset last_activity
        $this->get('/admin/dashboard');
        
        // Simulasikan waktu berlalu lebih dari ADMIN_IDLE_TIMEOUT (600 detik)
        session(['last_activity' => now()->subMinutes(15)->timestamp]);

        // Request berikutnya harus di-logout dan redirect ke login
        $response = $this->get('/admin/dashboard');
        
        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas.');
        $this->assertGuest();
    }
}
