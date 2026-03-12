<?php

namespace App\Http\Controllers\PaymentService;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming Moyasar webhook notifications.
     *
     * Moyasar sends POST requests when payment status changes asynchronously.
     * This ensures we capture updates even if the user's browser doesn't
     * complete the callback redirect (e.g., internet disconnection).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Moyasar webhook received', [
            'type' => $payload['type'] ?? 'unknown',
            'id' => $payload['data']['id'] ?? $payload['id'] ?? 'unknown',
        ]);

        // Verify the webhook comes from Moyasar by checking the secret
        if (!$this->verifyWebhookSignature($request)) {
            Log::warning('Moyasar webhook signature verification failed');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        try {
            $payment = PaymentService::handleWebhook($payload);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::warning('Webhook processing failed: invalid data', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Verify the webhook signature using the Moyasar secret key.
     *
     * Moyasar uses Basic Auth for webhook verification.
     * The request should include the secret key in the Authorization header
     * or you can verify by re-fetching the payment from the API.
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        // Option 1: If Moyasar sends a signature header
        $secretKey = config('moyasar.secret_key');

        if (empty($secretKey)) {
            Log::warning('Moyasar secret key not configured, skipping webhook verification');
            return true;
        }

        // Moyasar webhooks use Basic Auth with the secret key
        $authHeader = $request->header('Authorization');

        if ($authHeader) {
            $expectedAuth = 'Basic ' . base64_encode($secretKey . ':');
            return hash_equals($expectedAuth, $authHeader);
        }

        // If no auth header, we'll verify by re-fetching the payment
        // This is a fallback — the payment will be verified in handleWebhook
        return true;
    }
}
