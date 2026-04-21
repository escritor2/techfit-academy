<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recebe eventos de gateways de pagamento (Stripe, Mercado Pago, etc.)
 * e atualiza o status de assinaturas automaticamente.
 *
 * Para ativar, configure PAYMENT_WEBHOOK_SECRET no .env com o signing secret
 * fornecido pelo seu gateway de pagamento.
 */
class WebhookController extends Controller
{
    /**
     * Endpoint público para receber webhooks do Stripe.
     * Rota: POST /api/webhooks/stripe
     */
    public function stripe(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');

        // Valida a assinatura do webhook se o secret estiver configurado
        if ($secret) {
            $signature = $request->header('Stripe-Signature');
            if (!$this->verifyStripeSignature($request->getContent(), $signature, $secret)) {
                Log::warning('Webhook Stripe: assinatura inválida.');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $event = $request->all();
        $type  = $event['type'] ?? 'unknown';

        Log::info("Webhook Stripe recebido: {$type}");

        match ($type) {
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event),
            'invoice.payment_failed'    => $this->handlePaymentFailed($event),
            'customer.subscription.deleted' => $this->handleSubscriptionCancelled($event),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    /**
     * Endpoint público para receber webhooks do Mercado Pago.
     * Rota: POST /api/webhooks/mercadopago
     */
    public function mercadoPago(Request $request)
    {
        $event  = $request->all();
        $action = $event['action'] ?? 'unknown';

        Log::info("Webhook MercadoPago recebido: {$action}", $event);

        // Aqui você integra com a API do MP para buscar os detalhes do pagamento
        // $paymentId = $event['data']['id'] ?? null;
        // Buscar status via SDK do MP...

        return response()->json(['received' => true]);
    }

    // ─── Handlers Internos ─────────────────────────────────────────────────────

    private function handlePaymentSucceeded(array $event): void
    {
        $customerId = $event['data']['object']['customer'] ?? null;
        if (!$customerId) return;

        // Ativa a assinatura do cliente correspondente
        Subscription::where('stripe_customer_id', $customerId)
            ->where('status', '!=', 'active')
            ->update(['status' => 'active']);

        Log::info("Assinatura ativada para Stripe customer: {$customerId}");
    }

    private function handlePaymentFailed(array $event): void
    {
        $customerId = $event['data']['object']['customer'] ?? null;
        if (!$customerId) return;

        Subscription::where('stripe_customer_id', $customerId)
            ->where('status', 'active')
            ->update(['status' => 'past_due']);

        Log::warning("Pagamento falhou para Stripe customer: {$customerId}");
    }

    private function handleSubscriptionCancelled(array $event): void
    {
        $customerId = $event['data']['object']['customer'] ?? null;
        if (!$customerId) return;

        Subscription::where('stripe_customer_id', $customerId)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        Log::info("Assinatura cancelada para Stripe customer: {$customerId}");
    }

    // ─── Utilitários ──────────────────────────────────────────────────────────

    private function verifyStripeSignature(string $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) return false;

        $parts      = explode(',', $signature);
        $timestamp  = null;
        $signatures = [];

        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2);
            if ($key === 't') $timestamp  = $value;
            if ($key === 'v1') $signatures[] = $value;
        }

        if (!$timestamp || empty($signatures)) return false;

        $signedPayload = "{$timestamp}.{$payload}";
        $expected      = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) return true;
        }

        return false;
    }
}
