<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PaymentService
 *
 * Centralized service for Moyasar payment operations.
 * Handles initiation, verification, webhook processing, and config generation.
 */
class PaymentService
{
    /**
     * Initiate a new payment record before the user completes the form.
     *
     * @param array $data {
     *     @type int         $amount      Amount in halalas
     *     @type string|null $description Payment description
     *     @type array|null  $metadata    Arbitrary metadata (student_id, type, etc.)
     *     @type int|null    $user_id     Authenticated user ID
     *     @type string|null $moyasar_id  Moyasar payment ID (from on_completed hook)
     * }
     */
    public static function initiate(array $data): Payment
    {
        $payment = Payment::create([
            'user_id' => $data['user_id'] ?? null,
            'moyasar_id' => $data['moyasar_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => config('moyasar.currency', 'SAR'),
            'status' => 'initiated',
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'callback_url' => $data['callback_url'] ?? config('moyasar.callback_url'),
        ]);

        Log::info('Payment initiated', [
            'payment_id' => $payment->id,
            'moyasar_id' => $payment->moyasar_id,
            'amount' => $payment->amount,
        ]);

        return $payment;
    }

    /**
     * Verify a payment by calling Moyasar's GET /v1/payments/{id} endpoint.
     * Updates the local Payment record with the response data.
     */
    public static function verify(string $moyasarId): Payment
    {
        $baseUrl = config('moyasar.api_base_url');
        $secretKey = config('moyasar.secret_key');

        $response = Http::withBasicAuth($secretKey, '')
            ->get("{$baseUrl}/payments/{$moyasarId}");

        if (!$response->successful()) {
            Log::error('Moyasar verification failed', [
                'moyasar_id' => $moyasarId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Update existing record if found
            $payment = Payment::byMoyasarId($moyasarId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => 'Verification API call failed: ' . $response->status(),
                ]);
            }

            return $payment ?? self::createFailedRecord($moyasarId, 'Verification failed');
        }

        $paymentData = $response->json();

        return self::updateFromMoyasarResponse($moyasarId, $paymentData);
    }

    /**
     * Process an incoming Moyasar webhook payload.
     * Webhooks are sent asynchronously when a payment status changes.
     */
    public static function handleWebhook(array $payload): Payment
    {
        $moyasarId = $payload['data']['id'] ?? $payload['id'] ?? null;

        if (!$moyasarId) {
            Log::warning('Webhook received without payment ID', $payload);
            throw new \InvalidArgumentException('Missing payment ID in webhook payload');
        }

        Log::info('Processing Moyasar webhook', [
            'moyasar_id' => $moyasarId,
            'type' => $payload['type'] ?? 'unknown',
        ]);

        $data = $payload['data'] ?? $payload;

        return self::updateFromMoyasarResponse($moyasarId, $data);
    }

    /**
     * Build the configuration array for the Vue MoyasarPayment component.
     * This is passed as Inertia shared data or via an API response.
     *
     * @param int    $amount      Amount in halalas
     * @param string $description Payment description
     * @param array  $metadata    Metadata to associate with the payment
     */
    public static function getPaymentConfig(int $amount, string $description, array $metadata = []): array
    {
        return [
            'publishable_key' => config('moyasar.publishable_key'),
            'amount' => $amount,
            'currency' => config('moyasar.currency', 'SAR'),
            'description' => $description,
            'metadata' => $metadata,
            'callback_url' => url(config('moyasar.callback_url')),
            'supported_methods' => config('moyasar.supported_methods', ['mada', 'creditcard', 'applepay']),
        ];
    }

    // ─── Private Helpers ─────────────────────────────────────

    /**
     * Update or create a Payment record from a Moyasar API/webhook response.
     */
    private static function updateFromMoyasarResponse(string $moyasarId, array $data): Payment
    {
        $status = self::mapMoyasarStatus($data['status'] ?? 'unknown');

        $updateData = [
            'status' => $status,
            'payment_type' => $data['source']['type'] ?? null,
            'source_data' => $data['source'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? config('moyasar.currency'),
            'description' => $data['description'] ?? null,
        ];

        if ($status === 'paid') {
            $updateData['paid_at'] = now();
        }

        if (isset($data['source']['message']) && $status === 'failed') {
            $updateData['error_message'] = $data['source']['message'];
        }

        // Merge metadata from Moyasar response
        if (isset($data['metadata'])) {
            $updateData['metadata'] = $data['metadata'];
        }

        $payment = Payment::byMoyasarId($moyasarId)->first();

        if ($payment) {
            $payment->update($updateData);
            $payment->refresh();
        } else {
            $updateData['moyasar_id'] = $moyasarId;
            $payment = Payment::create($updateData);
        }

        Log::info('Payment updated from Moyasar', [
            'payment_id' => $payment->id,
            'moyasar_id' => $moyasarId,
            'status' => $status,
        ]);

        return $payment;
    }

    /**
     * Map Moyasar's payment status to our internal status.
     */
    private static function mapMoyasarStatus(string $moyasarStatus): string
    {
        return match ($moyasarStatus) {
            'paid' => 'paid',
            'failed' => 'failed',
            'authorized' => 'paid',
            'captured' => 'paid',
            'refunded' => 'refunded',
            'voided' => 'refunded',
            'initiated' => 'initiated',
            default => 'initiated',
        };
    }

    /**
     * Create a failed payment record when no existing record is found.
     */
    private static function createFailedRecord(string $moyasarId, string $errorMessage): Payment
    {
        return Payment::create([
            'moyasar_id' => $moyasarId,
            'amount' => 0,
            'currency' => config('moyasar.currency', 'SAR'),
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
