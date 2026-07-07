<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20|unique:customers',
            'email' => 'nullable|string|email|max:150|unique:customers',
            'loyalty_points' => 'integer|default:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $customer = Customer::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully.',
            'data' => $customer
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Customer not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20|unique:customers,phone,' . $id,
            'email' => 'nullable|string|email|max:150|unique:customers,email,' . $id,
            'loyalty_points' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $customer->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully.',
            'data' => $customer
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Customer not found'], 404);
        }

        $customer->delete(); // Soft delete

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully.'
        ]);
    }
}
