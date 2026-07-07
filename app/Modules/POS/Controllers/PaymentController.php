<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Order;
use App\Modules\POS\Models\PaymentMethod;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\ShiftCashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Process payment for an order and create sale records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id  (Order ID)
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method_id' => 'required|uuid',
            'amount_tendered' => 'nullable|numeric|min:0',
        ]);

        $order = Order::with('items')->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.'
            ], 404);
        }

        if ($order->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order has already been paid and completed.'
            ], 422);
        }

        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        if (!$paymentMethod) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payment method.'
            ], 422);
        }

        $amountTendered = $request->amount_tendered ?? $order->total;
        
        if ($amountTendered < $order->total && $paymentMethod->type === 'cash') {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount tendered is less than the order total.'
            ], 422);
        }

        $changeAmount = max(0, $amountTendered - $order->total);

        try {
            DB::beginTransaction();

            // Generate unique sale number: SALE-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $latestSale = Sale::whereDate('created_at', date('Y-m-d'))->latest('id')->first();
            $sequence = 1;
            if ($latestSale && preg_match('/-(\d+)$/', $latestSale->sale_number, $matches)) {
                $sequence = intval($matches[1]) + 1;
            }
            $saleNumber = 'SALE-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // 1. Create Sale Record
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'shift_id' => $order->shift_id,
                'user_id' => $request->user() ? $request->user()->id : $order->user_id, // Who processed the payment
                'customer_id' => $order->customer_id,
                'table_id' => $order->table_id,
                'order_type' => $order->order_type,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'tax_amount' => $order->tax_amount,
                'total' => $order->total,
                'sale_date' => date('Y-m-d'),
                'created_by' => $request->user() ? $request->user()->id : null,
            ]);

            // 2. Create Sale Payment Record
            $sale->payments()->create([
                'payment_method_id' => $paymentMethod->id,
                'amount' => $order->total,
                'amount_tendered' => $amountTendered,
                'change_amount' => $changeAmount,
                'currency_code' => 'USD',
                'exchange_rate' => 1,
                'created_by' => $request->user() ? $request->user()->id : null,
            ]);

            // 3. Record Cash Movement if applicable
            if ($paymentMethod->type === 'cash' && $order->shift_id) {
                ShiftCashMovement::create([
                    'shift_id' => $order->shift_id,
                    'type' => 'in',
                    'amount' => $order->total, // The amount actually kept
                    'note' => "Payment for Sale {$saleNumber}",
                    'created_by' => $request->user() ? $request->user()->id : null,
                ]);
            }

            // 4. Update Order Status
            $order->status = 'completed';
            $order->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully.',
                'data' => [
                    'sale' => $sale->load('payments'),
                    'order' => $order,
                    'change_amount' => $changeAmount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
