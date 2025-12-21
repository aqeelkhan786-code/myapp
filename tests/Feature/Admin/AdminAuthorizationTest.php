<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Property;
use App\Models\Room;
use App\Models\Booking;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role
        Role::firstOrCreate(['name' => 'admin']);
    }

    /** @test */
    public function non_authenticated_users_cannot_access_admin_routes()
    {
        $response = $this->get('/admin/bookings');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_without_admin_role_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/admin/bookings');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_users_can_access_admin_routes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/bookings');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_users_can_access_admin_bookings_index()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/bookings');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.bookings.index');
    }

    /** @test */
    public function admin_users_can_access_admin_properties()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/properties');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_users_can_access_admin_rooms()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/rooms');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_users_can_access_admin_settings()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/settings');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/admin/settings');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_users_can_access_admin_calendar()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/bookings/calendar/view');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_calendar()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/admin/bookings/calendar/view');
        
        $response->assertStatus(403);
    }
}


