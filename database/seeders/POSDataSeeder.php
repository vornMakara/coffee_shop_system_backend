<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\POS\Customer\Models\Customer;
use App\Modules\POS\Table\Models\TableCategory;
use App\Modules\POS\Table\Models\Table;
use App\Modules\POS\Payment\Models\PaymentMethod;

class POSDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the Main Branch
        $branch = Branch::where('code', 'HQ-01')->first();
        if (!$branch) {
            return;
        }

        // 1. Payment Methods
        PaymentMethod::firstOrCreate(
            ['name' => 'Cash', 'branch_id' => $branch->id],
            ['type' => 'cash', 'is_active' => true]
        );
        PaymentMethod::firstOrCreate(
            ['name' => 'Credit Card', 'branch_id' => $branch->id],
            ['type' => 'card', 'is_active' => true]
        );
        PaymentMethod::firstOrCreate(
            ['name' => 'KHQR', 'branch_id' => $branch->id],
            ['type' => 'qr', 'is_active' => true]
        );

        // 2. Customers
        Customer::firstOrCreate(
            ['first_name' => 'Walk-in', 'last_name' => 'Customer'],
            [
                'phone' => '000000000',
                'source' => 'walk_in',
            ]
        );

        Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1234567890',
                'source' => 'walk_in',
            ]
        );

        // 3. Table Categories
        $indoorCategory = TableCategory::firstOrCreate(
            ['name' => 'Indoor', 'branch_id' => $branch->id],
            ['sort_order' => 1]
        );
        
        $outdoorCategory = TableCategory::firstOrCreate(
            ['name' => 'Outdoor / Patio', 'branch_id' => $branch->id],
            ['sort_order' => 2]
        );

        // 4. Tables
        $indoorTables = ['T-01', 'T-02', 'T-03', 'T-04'];
        foreach ($indoorTables as $tableNumber) {
            Table::firstOrCreate(
                ['number' => $tableNumber, 'branch_id' => $branch->id],
                [
                    'table_category_id' => $indoorCategory->id,
                    'name' => 'Table ' . substr($tableNumber, -2),
                    'capacity' => 4,
                    'status' => 'available',
                    'floor' => '1st Floor'
                ]
            );
        }

        $outdoorTables = ['O-01', 'O-02'];
        foreach ($outdoorTables as $tableNumber) {
            Table::firstOrCreate(
                ['number' => $tableNumber, 'branch_id' => $branch->id],
                [
                    'table_category_id' => $outdoorCategory->id,
                    'name' => 'Table ' . substr($tableNumber, -2),
                    'capacity' => 2,
                    'status' => 'available',
                    'floor' => 'Patio'
                ]
            );
        }
    }
}
