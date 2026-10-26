<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function snapToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'gross_amount' => ['required', 'numeric', 'min:1'],
            'customer_name' => ['nullable', 'string'],
            'customer_email' => ['nullable', 'email'],
            'customer_phone' => ['nullable', 'string'],
        ]);

        $serverKey = env('MIDTRANS_SERVER_KEY');
        if (!$serverKey) {
            return response()->json([
                'ok' => false,
                'message' => 'MIDTRANS_SERVER_KEY belum diatur pada .env.',
            ], 422);
        }

        \Midtrans\Config::$serverKey = $serverKey;
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $payload = [
            'transaction_details' => [
                'order_id' => $data['order_id'],
                'gross_amount' => (int) $data['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => (string) ($data['customer_name'] ?? Auth::user()->username),
                'email' => (string) ($data['customer_email'] ?? Auth::user()->email),
                'phone' => (string) ($data['customer_phone'] ?? ''),
            ],
            'callbacks' => [
                'finish' => route('user.orders.track', ['resi' => $data['order_id']]),
                'error' => route('user.orders.track', ['resi' => $data['order_id']]),
                'pending' => route('user.orders.track', ['resi' => $data['order_id']]),
            ],
        ];

        try {
            $token = \Midtrans\Snap::getSnapToken($payload);
            return response()->json(['ok' => true, 'token' => $token]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal membuat Snap token.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $synced = $this->syncOrderStatusFromMidtrans($data['order_id']);

        return response()->json([
            'ok' => $synced['ok'],
            'status' => $synced['status'],
            'message' => $synced['message'],
        ], $synced['ok'] ? 200 : 422);
    }

    public function midtransCallback(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id', '');
        if ($orderId === '') {
            return response()->json(['ok' => false, 'message' => 'order_id tidak ditemukan.'], 422);
        }

        // Keep callback tolerant and rely on server-to-server re-check with Midtrans API.
        $synced = $this->syncOrderStatusFromMidtrans($orderId);
        if (!$synced['ok']) {
            Log::warning('Midtrans callback sync failed', ['order_id' => $orderId, 'message' => $synced['message']]);
        }

        return response()->json(['ok' => $synced['ok'], 'message' => $synced['message']]);
    }

    private function syncOrderStatusFromMidtrans(string $orderId): array
    {
        $serverKey = (string) env('MIDTRANS_SERVER_KEY');
        if ($serverKey === '') {
            return ['ok' => false, 'status' => 'pending', 'message' => 'MIDTRANS_SERVER_KEY belum diatur.'];
        }

        $url = (bool) env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://api.midtrans.com/v2/' . rawurlencode($orderId) . '/status'
            : 'https://api.sandbox.midtrans.com/v2/' . rawurlencode($orderId) . '/status';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            return ['ok' => false, 'status' => 'pending', 'message' => 'Gagal verifikasi Midtrans: ' . $curlErr];
        }

        $json = json_decode((string) $response, true);
        $transactionStatus = strtolower((string) ($json['transaction_status'] ?? 'pending'));
        $fraudStatus = strtolower((string) ($json['fraud_status'] ?? 'accept'));

        $mapped = 'pending';
        if (in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus !== 'deny') {
            $mapped = 'terbayar';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $mapped = 'cancelled';
        }

        Sale::query()->where('resi', $orderId)->update([
            'status' => $mapped,
        ]);

        return ['ok' => true, 'status' => $mapped, 'message' => 'Status sinkron.'];
    }
}
