<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingApiController extends Controller
{
    public function __construct(private readonly RajaOngkirService $rajaOngkir)
    {
    }

    public function destinations(Request $request): JsonResponse
    {
        $query = trim((string) $request->string('query'));
        if ($query === '') {
            return response()->json(['ok' => false, 'message' => 'Query wajib diisi.'], 422);
        }

        $result = $this->rajaOngkir->searchDestinations($query, 10);
        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    public function estimate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'destination' => ['required', 'string'],
            'courier' => ['nullable', 'string'],
            'weight' => ['nullable', 'integer', 'min:1'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $weight = (int) ($data['weight'] ?? 0);
        if ($weight < 1 && !empty($data['product_id'])) {
            $product = Product::query()->findOrFail((int) $data['product_id']);
            $weight = max(1, (int) $product->weight_gram) * max(1, (int) ($data['quantity'] ?? 1));
        }

        if ($weight < 1) {
            $weight = 1000;
        }

        $result = $this->rajaOngkir->estimateCost(
            (string) $data['destination'],
            $weight,
            (string) ($data['courier'] ?? 'jne')
        );

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
