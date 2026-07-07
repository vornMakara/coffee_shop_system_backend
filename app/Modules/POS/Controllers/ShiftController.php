<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Get the current open shift for the authenticated user.
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
     * Open a new shift.
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
