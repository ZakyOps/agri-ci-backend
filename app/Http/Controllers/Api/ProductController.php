<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Product::query()->with('category.parent')->orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return response()->json(['success' => true, 'data' => $product], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $product->load('category.parent')]);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json(['success' => true, 'data' => $product->refresh()]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
