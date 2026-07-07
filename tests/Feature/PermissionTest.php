<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run migrations and sync permissions
        Artisan::call('migrate');
        Artisan::call('rbac:sync');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_admin_has_all_permissions()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->assertTrue($admin->hasPermission('pos.access'));
        $this->assertTrue($admin->hasPermission('pos.void'));
        $this->assertTrue($admin->hasPermission('admin.roles'));
        
        $permissionsArray = $admin->all_permissions;
        $this->assertNotEmpty($permissionsArray);
        $this->assertContains('pos.access', $permissionsArray);
    }

    public function test_waiter_cannot_access_admin_routes()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiter = User::factory()->create(['role_id' => $waiterRole->id]);

        $token = auth('api')->login($waiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/permissions');

        $response->assertStatus(403);
    }

    public function test_waiter_cannot_void_order()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiter = User::factory()->create(['role_id' => $waiterRole->id]);

        $token = auth('api')->login($waiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/v1/orders/random-uuid');

        $response->assertStatus(403);
    }
}
