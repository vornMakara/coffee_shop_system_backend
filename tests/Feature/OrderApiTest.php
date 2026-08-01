<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\User\Models\User;
use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Auth\Role\Models\Role;
use App\Modules\POS\Order\Models\Order;
use App\Modules\POS\Shift\Models\Shift;
use App\Modules\POS\Payment\Models\Sale;
use App\Modules\POS\Payment\Models\PaymentMethod;
use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Product\Models\Product;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $product;
    protected $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();
        
        Shift::query()->delete();
        Sale::withTrashed()->forceDelete();
        Order::withTrashed()->forceDelete();
        
        $branch = Branch::firstOrCreate(
            ['code' => 'MAIN-01'],
            ['name' => 'Main Branch']
        );

        $role = Role::firstOrCreate(
            ['name' => 'Cashier'],
            ['display_name' => 'Cashier', 'permissions' => json_encode(['pos.access'])]
        );

        $user = User::updateOrCreate(
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

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'cashier@coffeeshop.com',
            'password' => 'secret123'
        ]);
        
        $this->token = $response->json('authorization.token');

        $category = Category::firstOrCreate(
            ['name' => 'Hot Coffee', 'branch_id' => $branch->id],
            ['is_active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'ESP-01', 'branch_id' => $branch->id],
            [
                'name' => 'Espresso',
                'category_id' => $category->id,
                'selling_price' => 3.50,
                'is_active' => true
            ]
        );

        $this->paymentMethod = PaymentMethod::firstOrCreate(
            ['name' => 'Cash'],
            ['type' => 'cash', 'is_active' => true]
        );
    }

    public function test_can_create_order_publicly()
    {
        $response = $this->postJson('/api/v1/orders', [
            'order_type' => 'dine_in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.total', 7); // 2 * 3.50

        $orderNumber = $response->json('data.order_number');
        $this->assertNotEmpty($orderNumber);

        // Test tracking order (Public GET)
        $trackResponse = $this->getJson("/api/v1/orders/{$orderNumber}");
        $trackResponse->assertStatus(200)
                      ->assertJsonPath('data.status', 'pending');
    }

    public function test_staff_can_update_order_status_and_pay()
    {
        $this->withoutExceptionHandling();
        // 1. Create order
        $orderNumber = 'ORD-TEST-' . uniqid();
        $order = Order::create([
            'order_number' => $orderNumber,
            'order_type' => 'takeaway',
            'status' => 'pending',
            'subtotal' => 3.50,
            'total' => 3.50,
            'user_id' => User::first()->id
        ]);
        $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => 'Espresso',
            'quantity' => 1,
            'unit_price' => 3.50,
            'line_total' => 3.50
        ]);

        // 2. Update status
        $statusResponse = $this->patchJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'ready'
        ], ['Authorization' => "Bearer {$this->token}"]);
        
        $statusResponse->assertStatus(200)
                       ->assertJsonPath('data.status', 'ready');

        // 3. Process payment
        $payResponse = $this->postJson("/api/v1/orders/{$order->id}/pay", [
            'payment_method_id' => $this->paymentMethod->id,
            'amount_tendered' => 5.00
        ], ['Authorization' => "Bearer {$this->token}"]);

        if ($payResponse->status() !== 200) {
            dd($payResponse->json());
        }
        $payResponse->assertStatus(200)
                    ->assertJsonPath('status', 'success')
                    ->assertJsonPath('data.change_amount', 1.50)
                    ->assertJsonPath('data.order.status', 'completed');
                    
        $saleId = $payResponse->json('data.sale.id');
        $this->assertNotEmpty($saleId);
    }
}
