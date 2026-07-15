<?php

namespace App\Modules\POS\Shift\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Shift\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/shifts/current",
     *     tags={"POS Core Data"},
     *     summary="Get Current Shift",
     *     description="Checks if the current cashier has an active shift.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current shift retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="opened_at", type="string", example="2023-10-01 08:00:00"),
     *                 @OA\Property(property="starting_cash", type="number", format="float", example=150.00)
     *             )
     *         )
     *     )
     * )
     */
    public function current(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$shift) {
            return response()->json([
                'status' => 'success',
                'message' => 'No open shift found.',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Current shift retrieved successfully.',
            'data' => $shift
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shifts/open",
     *     tags={"POS Core Data"},
     *     summary="Open Shift",
     *     description="Opens the register for the cashier with a starting float.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="opening_cash", type="number", format="float", example=150.00),
     *             @OA\Property(property="opening_cash_khr", type="number", format="float", example=400000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Shift opened successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Shift opened successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="uuid")
     *             )
     *         )
     *     )
     * )
     */
    public function open(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'opening_cash_khr' => 'nullable|numeric|min:0'
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Check if there is already an open shift
        $existingShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have an open shift.',
                'data' => $existingShift
            ], 400);
        }

        $shift = Shift::create([
            'branch_id' => $user->branch_id, // If branch_id is available on user
            'user_id' => $user->id,
            'opening_cash' => $request->opening_cash,
            'opening_cash_khr' => $request->opening_cash_khr ?? 0,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Shift opened successfully.',
            'data' => $shift
        ], 201);
    }
}
