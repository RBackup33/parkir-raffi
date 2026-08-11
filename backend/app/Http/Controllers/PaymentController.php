<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function bayar(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Payment controller ready'
        ]);
    }
}