<?php

namespace App\Modules\POS\Customer\Services\Admin;

use App\Modules\POS\Customer\Models\Customer;

class CustomerService
{
    public function getPaginated(int $perPage = 15)
    {
        return Customer::with('branch')->paginate($perPage);
    }

    public function createCustomer(array $data)
    {
        return Customer::create($data)->load('branch');
    }

    public function updateCustomer(Customer $customer, array $data)
    {
        $customer->update($data);
        return $customer->load('branch');
    }

    public function deleteCustomer(Customer $customer)
    {
        return $customer->delete();
    }
}
