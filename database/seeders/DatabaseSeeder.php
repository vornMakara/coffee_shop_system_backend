<?php

namespace Database\Seeders;

use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Auth\Role\Models\Role;
use App\Modules\Auth\User\Models\User;
use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Product\Models\Product;
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
        // 1. Run Roles and Permissions Seeder
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Create Main Branch
        $branch = Branch::firstOrCreate(
            ['code' => 'HQ-01'],
            [
                'name' => 'HQ Coffee Shop',
                'address' => '123 Coffee Street, Downtown',
                'phone' => '123-456-7890',
                'is_active' => true,
            ]
        );

        // 3. Get Roles created by RolesAndPermissionsSeeder
        $adminRole = Role::where('name', 'Admin')->first();
        $cashierRole = Role::where('name', 'Cashier')->first();

        // 4. Create Users (Matching Swagger Documentation Test Accounts)
        User::firstOrCreate(
            ['email' => 'admin@coffeeshop.com'],
            [
                'branch_id' => $branch->id,
                'role_id' => $adminRole->id,
                'username' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'password' => Hash::make('password123'), // As specified in Swagger Docs
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@coffeeshop.com'],
            [
                'branch_id' => $branch->id,
                'role_id' => $cashierRole->id,
                'username' => 'cashier1',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        // 5. Create Categories
        $hotCoffee = Category::firstOrCreate(['name' => 'Hot Coffee', 'branch_id' => $branch->id], ['sort_order' => 1]);
        $coldBrew = Category::firstOrCreate(['name' => 'Cold Brew & Iced', 'branch_id' => $branch->id], ['sort_order' => 2]);
        $pastries = Category::firstOrCreate(['name' => 'Pastries', 'branch_id' => $branch->id], ['sort_order' => 3]);

        // 6. Create 10 Dummy Products
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
            Product::firstOrCreate(
                ['name' => $product['name'], 'branch_id' => $branch->id],
                [
                    'category_id' => $product['category_id'],
                    'selling_price' => $product['selling_price'],
                    'is_active' => true,
                ]
            );
        }

        // 7. Run POS Data Seeder
        $this->call(POSDataSeeder::class);
    }
}
