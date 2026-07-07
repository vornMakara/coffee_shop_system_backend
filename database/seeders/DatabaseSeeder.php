<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Branch;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Main Branch
        $branch = Branch::create([
            'name' => 'HQ Coffee Shop',
            'code' => 'HQ-01',
            'address' => '123 Coffee Street, Downtown',
            'phone' => '123-456-7890',
            'is_active' => true,
        ]);

        // 2. Create Roles
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'is_active' => true,
        ]);

        $cashierRole = Role::create([
            'name' => 'cashier',
            'display_name' => 'Cashier',
            'is_active' => true,
        ]);

        // 3. Create Users
        User::create([
            'branch_id' => $branch->id,
            'role_id' => $adminRole->id,
            'username' => 'admin',
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@coffeeshop.com',
            'password' => Hash::make('password123'), // Default password
            'is_active' => true,
        ]);

        User::create([
            'branch_id' => $branch->id,
            'role_id' => $cashierRole->id,
            'username' => 'cashier1',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'cashier@coffeeshop.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // 4. Create Categories
        $hotCoffee = Category::create(['branch_id' => $branch->id, 'name' => 'Hot Coffee', 'sort_order' => 1]);
        $coldBrew = Category::create(['branch_id' => $branch->id, 'name' => 'Cold Brew & Iced', 'sort_order' => 2]);
        $pastries = Category::create(['branch_id' => $branch->id, 'name' => 'Pastries', 'sort_order' => 3]);

        // 5. Create 10 Dummy Products
        $products = [
            // Hot Coffees
            ['category_id' => $hotCoffee->id, 'name' => 'Espresso', 'selling_price' => 2.50],
            ['category_id' => $hotCoffee->id, 'name' => 'Americano', 'selling_price' => 3.00],
            ['category_id' => $hotCoffee->id, 'name' => 'Cappuccino', 'selling_price' => 4.50],
            ['category_id' => $hotCoffee->id, 'name' => 'Latte', 'selling_price' => 4.50],
            ['category_id' => $hotCoffee->id, 'name' => 'Mocha', 'selling_price' => 5.00],
            
            // Cold Coffees
            ['category_id' => $coldBrew->id, 'name' => 'Iced Americano', 'selling_price' => 3.50],
            ['category_id' => $coldBrew->id, 'name' => 'Iced Latte', 'selling_price' => 5.00],
            ['category_id' => $coldBrew->id, 'name' => 'Cold Brew', 'selling_price' => 4.00],
            
            // Pastries
            ['category_id' => $pastries->id, 'name' => 'Butter Croissant', 'selling_price' => 3.50],
            ['category_id' => $pastries->id, 'name' => 'Chocolate Chip Cookie', 'selling_price' => 2.50],
        ];

        foreach ($products as $product) {
            Product::create([
                'branch_id' => $branch->id,
                'category_id' => $product['category_id'],
                'name' => $product['name'],
                'selling_price' => $product['selling_price'],
                'is_active' => true,
            ]);
        }
    }
}
