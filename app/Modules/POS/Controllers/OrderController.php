<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\POS\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Retrieve a list of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Optionally order by latest
        $orders = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Orders retrieved successfully.',
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|uuid',
            'shift_id' => 'nullable|uuid',
            'customer_id' => 'nullable|uuid',
            'table_id' => 'nullable|uuid',
            'order_type' => 'required|in:dine_in,takeaway,delivery,drive_thru',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selected_modifiers' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $tax_amount = 0; // Keeping simple for MVP
            
            // Generate unique order number: ORD-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $latestOrder = Order::whereDate('created_at', date('Y-m-d'))->latest('id')->first();
            $sequence = 1;
            if ($latestOrder && preg_match('/-(\d+)$/', $latestOrder->order_number, $matches)) {
                $sequence = intval($matches[1]) + 1;
            }
            $orderNumber = 'ORD-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'branch_id' => $request->branch_id,
                'shift_id' => $request->shift_id,
                'user_id' => $request->user() ? $request->user()->id : null, // If order created via customer app, user might be null, but schema says user_id is NOT NULL in sales? In orders it is uuid('user_id'). Let's assume a default POS user if customer ordered.
                'customer_id' => $request->customer_id,
                'table_id' => $request->table_id,
                'order_type' => $request->order_type,
                'status' => 'pending',
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Base calculation
                $unitPrice = $product->selling_price;
                $quantity = $item['quantity'];
                
                // TODO: Add modifier prices if applicable
                
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost_price' => $product->cost_price ?? 0,
                    'line_total' => $lineTotal,
                    'selected_modifiers' => $item['selected_modifiers'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Calculate final totals
            $total = $subtotal + $tax_amount;
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully.',
                'data' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order by order_number.
     * This endpoint is meant to be public so customers can track their order.
     *
     * @param  string  $orderNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($identifier)
    {
        $query = Order::with('items');
        
        if (Str::isUuid($identifier)) {
            $query->where('id', $identifier);
        } else {
            $query->where('order_number', $identifier);
        }
        
        $order = $query->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully.',
            'data' => $order
        ]);
    }

    /**
     * Update the status of the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled,voided'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.'
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully.',
            'data' => $order
        ]);
    }

    /**
     * Cancel/Delete an order.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.'
            ], 404);
        }

        // Soft delete the order
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully.'
        ]);
    }
}
