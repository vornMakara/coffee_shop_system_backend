<?php

namespace Tests\Feature;

use App\Modules\Auth\Permission\Models\Permission;
use App\Modules\Auth\Role\Models\Role;
use App\Modules\Auth\User\Models\User;
use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Catalog\Category\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
        Artisan::call('rbac:sync');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_category()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        
        
        $branch = Branch::create([
            'name' => 'Main Branch ' . uniqid(),
        ]);
        
        $admin = User::create([
            'username' => 'admin' . uniqid(),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'branch_id' => $branch->id,
        ]);

        $token = auth('api')->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/admin/categories', [
            'name' => 'Cold Brews',
            'description' => 'Cold brewed coffee',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'name' => 'Cold Brews'
        ]);
    }

    public function test_waiter_cannot_create_category()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        
        $branch = Branch::create([
            'name' => 'Main Branch ' . uniqid(),
        ]);
        
        $waiter = User::create([
            'username' => 'waiter' . uniqid(),
            'first_name' => 'Waiter',
            'last_name' => 'User',
            'email' => 'waiter' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $branch->id,
        ]);

        $token = auth('api')->login($waiter);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/admin/categories', [
            'name' => 'Iced Teas',
            'branch_id' => $branch->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_soft_delete_category()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        
        $branch = Branch::create([
            'name' => 'Main Branch ' . uniqid(),
        ]);
        
        $admin = User::create([
            'username' => 'admin2' . uniqid(),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin2' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'branch_id' => $branch->id,
        ]);
        
        $category = Category::create([
            'name' => 'Test Category ' . uniqid(),
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $token = auth('api')->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/v1/admin/categories/' . $category->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('categories', [
            'id' => $category->id
        ]);
    }
}
