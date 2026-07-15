<?php

namespace App\Modules\POS\Customer\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\POS\Customer\Models\Customer;
use App\Modules\POS\Customer\Requests\Admin\StoreCustomerRequest;
use App\Modules\POS\Customer\Requests\Admin\UpdateCustomerRequest;
use App\Modules\POS\Customer\Resources\Admin\CustomerResource;
use App\Modules\POS\Customer\Services\Admin\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/admin/customers",
     *     tags={"Customers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Customers",
     *     description="Retrieve a paginated list of customers. Requires `admin.customers` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Customers retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Customers retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Jane Smith"),
     *                         @OA\Property(property="phone", type="string", example="+1987654321"),
     *                         @OA\Property(property="email", type="string", example="jane@example.com"),
     *                         @OA\Property(property="loyalty_points", type="integer", example=150),
     *                         @OA\Property(property="id", type="string", example="uuid"),
     *                         @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $customers = $this->customerService->getPaginated(15);
        return response()->json([
            'status' => 'success',
            'message' => 'Customers retrieved successfully.',
            'data' => CustomerResource::collection($customers)->response()->getData(true)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/customers",
     *     tags={"Customers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Customer",
     *     description="Create a new Customer. Requires `admin.customers` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_id", type="string", format="uuid"),
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Smith"),
     *             @OA\Property(property="phone", type="string", example="+1987654321"),
     *             @OA\Property(property="email", type="string", example="jane@example.com"),
     *             @OA\Property(property="loyalty_points", type="integer", example=150)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Created successfully.")
     *         )
     *     )
     * )
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->createCustomer($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => new CustomerResource($customer)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/customers/{id}",
     *     tags={"Customers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get Customer",
     *     description="Retrieve a single Customer by ID. Requires `admin.customers` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Details retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Details retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Jane Smith"),
     *                 @OA\Property(property="phone", type="string", example="+1987654321"),
     *                 @OA\Property(property="email", type="string", example="jane@example.com"),
     *                 @OA\Property(property="loyalty_points", type="integer", example=150),
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $customer = Customer::with('branch')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => new CustomerResource($customer)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/customers/{id}",
     *     tags={"Customers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Customer",
     *     description="Update an existing Customer. Requires `admin.customers` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Smith"),
     *             @OA\Property(property="phone", type="string", example="+1987654321"),
     *             @OA\Property(property="email", type="string", example="jane@example.com"),
     *             @OA\Property(property="loyalty_points", type="integer", example=150)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Updated successfully.")
     *         )
     *     )
     * )
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer = $this->customerService->updateCustomer($customer, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => new CustomerResource($customer)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/customers/{id}",
     *     tags={"Customers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete Customer",
     *     description="Soft-delete a Customer. Requires `admin.customers` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deleted successfully.")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerService->deleteCustomer($customer);

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }
}
