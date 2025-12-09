<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuotationStatus;
use Illuminate\Http\JsonResponse;

class QuotationStatusListController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = QuotationStatus::all(['status_id', 'name', 'description', 'color', 'is_active']);
        return response()->json($statuses);
    }
}
