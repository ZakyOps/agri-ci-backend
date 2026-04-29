<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Setting::query()->orderBy('key')->get(),
        ]);
    }

    public function update(string $key): JsonResponse
    {
        $data = request()->validate([
            'value' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = Setting::query()->updateOrCreate(['key' => $key], $data);

        return response()->json(['success' => true, 'data' => $setting]);
    }
}
