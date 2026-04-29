<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Models\Farmer;
use App\Models\Transaction;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function store(TransactionRequest $request, CheckoutService $checkoutService): JsonResponse
    {
        $farmer = Farmer::query()->findOrFail($request->integer('farmer_id'));

        $transaction = $checkoutService->checkout(
            $farmer,
            $request->user()->id,
            $request->validated('items'),
            $request->validated('payment_method'),
        );

        return response()->json(['success' => true, 'data' => $transaction], 201);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $transaction->load(['farmer', 'operator', 'items.product', 'debt']),
        ]);
    }
}
