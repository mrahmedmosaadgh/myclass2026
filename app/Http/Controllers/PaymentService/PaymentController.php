<?php

namespace App\Http\Controllers\PaymentService;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PaymentController extends Controller
{
    /**
     * Display the payment page with Moyasar configuration.
     */
    public function index(Request $request): InertiaResponse
    {
        $paymentConfig = PaymentService::getPaymentConfig(
            amount: (int) $request->get('amount', 5000),
            description: $request->get('description', 'MyClass Payment'),
            metadata: $request->get('metadata', []),
        );

        $payments = Payment::forUser(Auth::id())
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('PaymentService/PaymentPage', [
            'paymentConfig' => $paymentConfig,
            'payments' => $payments,
        ]);
    }

    /**
     * Initiate a payment record.
     * Called from the Vue component's on_completed hook before 3DS redirect.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'moyasar_id' => 'required|string',
            'amount' => 'required|integer|min:100',
            'description' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        try {
            $payment = PaymentService::initiate([
                'user_id' => Auth::id(),
                'moyasar_id' => $validated['moyasar_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment.',
            ], 500);
        }
    }

    /**
     * Handle the callback after Moyasar 3DS redirect.
     * Moyasar redirects here with ?id=<moyasar_payment_id>&status=<status>.
     */
    public function callback(Request $request): InertiaResponse
    {
        $moyasarId = $request->query('id');
        $status = $request->query('status');
        $message = $request->query('message', '');

        if (!$moyasarId) {
            return Inertia::render('PaymentService/PaymentCallback', [
                'payment' => null,
                'error' => 'No payment ID received from gateway.',
            ]);
        }

        try {
            $payment = PaymentService::verify($moyasarId);

            return Inertia::render('PaymentService/PaymentCallback', [
                'payment' => [
                    'id' => $payment->id,
                    'moyasar_id' => $payment->moyasar_id,
                    'amount' => $payment->getAmountInMainUnit(),
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_type' => $payment->payment_type,
                    'description' => $payment->description,
                    'metadata' => $payment->metadata,
                    'paid_at' => $payment->paid_at?->toISOString(),
                ],
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment callback verification failed', [
                'moyasar_id' => $moyasarId,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('PaymentService/PaymentCallback', [
                'payment' => null,
                'error' => 'Payment verification failed. Please contact support.',
            ]);
        }
    }

    /**
     * Check payment status via AJAX.
     */
    public function status(Payment $payment): JsonResponse
    {
        // Ensure the authenticated user owns this payment
        if ($payment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $payment->id,
            'moyasar_id' => $payment->moyasar_id,
            'status' => $payment->status,
            'amount' => $payment->getAmountInMainUnit(),
            'currency' => $payment->currency,
            'payment_type' => $payment->payment_type,
            'paid_at' => $payment->paid_at?->toISOString(),
        ]);
    }
}
