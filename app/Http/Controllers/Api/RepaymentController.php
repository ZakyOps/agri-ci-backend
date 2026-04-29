<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RepaymentRequest;
use App\Models\Farmer;
use App\Services\RepaymentService;
use Illuminate\Http\JsonResponse;

class RepaymentController extends Controller
{
    public function store(RepaymentRequest $request, RepaymentService $repaymentService): JsonResponse
    {
        $farmer = Farmer::query()->findOrFail($request->integer('farmer_id'));

        $repayment = $repaymentService->repay(
            $farmer,
            $request->user()->id,
            (float) $request->validated('commodity_kg'),
        );

        return response()->json(['success' => true, 'data' => $repayment], 201);
    }

    public function farmerRepayments(Farmer $farmer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $farmer->repayments()->with('allocations.debt')->latest()->get(),
        ]);
    }
}
