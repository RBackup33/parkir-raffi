<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Scan Controller Ready'
        ]);
    }
}