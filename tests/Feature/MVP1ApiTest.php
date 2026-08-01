<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\User\Models\User;
use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Auth\Role\Models\Role;
use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Product\Models\Product;
use App\Modules\POS\Shift\Models\Shift;
use App\Modules\POS\Customer\Models\Customer;
use App\Modules\POS\Table\Models\TableCategory;
use App\Modules\POS\Table\Models\Table;

class MVP1ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear shifts to avoid state leakage
        Shift::query()->delete();
        
        // Setup a branch
        $branch = Branch::firstOrCreate(
            ['code' => 'MAIN-01'],
            [
                'name' => 'Main Branch',
                'address' => '123 Coffee St',
                'phone' => '1234567890'
            ]
        );

        // Setup a role
        $role = Role::firstOrCreate(
            ['name' => 'Cashier'],
            [
                'display_name' => 'Cashier',
                'permissions' => json_encode(['pos.access'])
            ]
        );

        // Setup cashier user
        User::updateOrCreate(
            ['email' => 'cashier@coffeeshop.com'],
            [
                'username' => 'cashier',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => \Hash::make('secret123'),
                'branch_id' => $branch->id,
                'role_id' => $role->id,
                'is_active' => true
            ]
        );

        // Setup a Category and Product
        $category = Category::firstOrCreate(
            ['name' => 'Hot Coffee', 'branch_id' => $branch->id],
            [
                'description' => 'Hot brewed coffee',
                'sort_order' => 1,
                'is_active' => true
            ]
        );

        Product::firstOrCreate(
            ['name' => 'Latte', 'branch_id' => $branch->id],
            [
                'category_id' => $category->id,
                'description' => 'Milk coffee',
                'selling_price' => 3.50,
                'cost_price' => 1.00,
                'is_active' => true
            ]
        );

        // Setup Customers
        Customer::firstOrCreate(
            ['phone' => '555-1234'],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'points' => 0
            ]
        );

        // Setup Table Category
        $indoorCategory = TableCategory::firstOrCreate(
            ['name' => 'Indoor', 'branch_id' => $branch->id],
            ['sort_order' => 1]
        );
        $outdoorCategory = TableCategory::firstOrCreate(
            ['name' => 'Outdoor', 'branch_id' => $branch->id],
            ['sort_order' => 2]
        );

        // Setup Tables
        Table::firstOrCreate(
            ['number' => 'T1', 'branch_id' => $branch->id],
            [
                'table_category_id' => $indoorCategory->id,
                'name' => 'Window 1',
                'capacity' => 4,
                'status' => 'available'
            ]
        );
        
        Table::firstOrCreate(
            ['number' => 'P1', 'branch_id' => $branch->id],
            [
                'table_category_id' => $outdoorCategory->id,
                'name' => 'Patio 1',
                'capacity' => 6,
                'status' => 'available'
            ]
        );
    }

    private function getCashier()
    {
        return User::where('email', 'cashier@coffeeshop.com')->first();
    }

    public function test_auth_login_returns_token()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'cashier@coffeeshop.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'access_token',
                         'token_type',
                         'expires_in',
                         'user'
                     ]
                 ]);
    }

    public function test_can_list_table_categories()
    {
        $user = $this->getCashier();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/tables/categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success'])
                 ->assertJsonStructure(['data'])
                 ->assertSee('Indoor')
                 ->assertSee('Outdoor');
    }

    public function test_can_list_tables()
    {
        $user = $this->getCashier();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/tables');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success'])
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'table_category_id', 'status']
                     ]
                 ]);
    }

    public function test_can_search_customers()
    {
        $user = $this->getCashier();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/customers?search=John');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success'])
                 ->assertSee('John');
    }

    public function test_can_list_catalog_categories()
    {
        $user = $this->getCashier();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success'])
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name']
                     ]
                 ]);
    }

    public function test_can_list_catalog_products()
    {
        $user = $this->getCashier();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success'])
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'selling_price']
                     ]
                 ]);
    }

    public function test_can_open_shift()
    {
        $user = $this->getCashier();

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/shifts/open', [
            'opening_cash' => 100.50
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['status' => 'success']);

        // Test getting current shift
        $response = $this->actingAs($user, 'api')->getJson('/api/v1/shifts/current');
        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success']);
        
        $this->assertEquals(100.50, $response->json('data.opening_cash'));
    }
}
