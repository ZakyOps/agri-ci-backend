<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FarmerRequest;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;

class FarmerController extends Controller
{
    public function index(): JsonResponse
    {
        $farmers = Farmer::query()
            ->latest()
            ->get()
            ->map(fn (Farmer $farmer) => $this->withDebtSummary($farmer));

        return response()->json(['success' => true, 'data' => $farmers]);
    }

    public function store(FarmerRequest $request): JsonResponse
    {
        $farmer = Farmer::query()->create($request->validated());

        return response()->json(['success' => true, 'data' => $this->withDebtSummary($farmer)], 201);
    }

    public function show(Farmer $farmer): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->withDebtSummary($farmer)]);
    }

    public function update(FarmerRequest $request, Farmer $farmer): JsonResponse
    {
        $farmer->update($request->validated());

        return response()->json(['success' => true, 'data' => $this->withDebtSummary($farmer->refresh())]);
    }

    public function search(): JsonResponse
    {
        $query = request()->validate([
            'query' => ['required', 'string', 'max:255'],
        ])['query'];

        $farmers = Farmer::query()
            ->where('identifier', $query)
            ->orWhere('phone', $query)
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn (Farmer $farmer) => $this->withDebtSummary($farmer));

        return response()->json(['success' => true, 'data' => $farmers]);
    }

    public function debts(Farmer $farmer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $farmer->debts()
                ->where('status', '!=', 'paid')
                ->with('transaction.items.product')
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    private function withDebtSummary(Farmer $farmer): array
    {
        return array_merge($farmer->toArray(), [
            'outstanding_debt_fcfa' => $farmer->outstandingDebtFcfa(),
        ]);
    }
}
