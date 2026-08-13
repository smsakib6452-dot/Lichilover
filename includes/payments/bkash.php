<?php
declare(strict_types=1);

/**
 * bKash payment gateway (tokenized checkout).
 *
 * Demo mode: returns a simulated flow clearly labelled "demo".
 * Live mode: uses the bKash Tokenized Checkout API (grant → create → execute).
 */

require_once __DIR__ . '/gateway.php';

class BkashGateway extends PaymentGateway
{
    public function name(): string
    {
        return 'bKash';
    }

    public function methodKey(): string
    {
        return 'bkash';
    }

    public function initiate(array $order, string $ip = ''): array
    {
        if ($this->isDemo()) {
            return [
                'status'    => 'pending',
                'demo'      => true,
                'reference' => 'DEMO-BKASH-' . $order['order_number'],
                'message'   => 'Demo payment mode — no real money will be charged.',
            ];
        }

        try {
            $token = $this->getToken();
            $callbackUrl = url('payment-callback.php?method=bkash&payment=' . ($order['payment_id'] ?? '') . '&order=' . urlencode($order['order_number']));

            $resp = $this->http(BKASH_BASE_URL . '/tokenized/checkout/create', [
                'mode'                  => '0011',
                'payerReference'        => (string) $order['id'],
                'callbackURL'           => $callbackUrl,
                'amount'                => (string) number_format((float) $order['total'], 2, '.', ''),
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $order['order_number'],
            ], 'POST', [
                'Authorization: ' . $token,
                'X-APP-Key: ' . BKASH_APP_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ]);

            $data = json_decode($resp['body'], true);

            if (!empty($data['bkashURL'])) {
                return [
                    'status'    => 'processing',
                    'redirect'  => $data['bkashURL'],
                    'reference' => $data['paymentID'] ?? ('BKASH-' . $order['order_number']),
                ];
            }
            return ['status' => 'failed', 'message' => $data['statusMessage'] ?? 'bKash gateway error'];
        } catch (Throwable $e) {
            log_app('bKash initiate error: ' . $e->getMessage());
            return ['status' => 'failed', 'message' => 'Could not reach bKash. Please try again or choose another method.'];
        }
    }

    public function verify(array $payment, array $request = []): array
    {
        if ($this->isDemo()) {
            // Simulated server-side verification for demo payments.
            $reference = $payment['payment_id'] ?? '';
            return [
                'status'         => 'paid',
                'transaction_id' => $reference,
                'message'        => 'Demo bKash transaction simulated. No real money was charged.',
            ];
        }

        try {
            $token = $this->getToken();
            $paymentId = $request['paymentID'] ?? $payment['payment_id'] ?? '';
            if (!$paymentId) {
                return ['status' => 'failed', 'message' => 'Missing payment ID'];
            }
            $resp = $this->http(BKASH_BASE_URL . '/tokenized/checkout/execute', [
                'paymentID' => $paymentId,
            ], 'POST', [
                'Authorization: ' . $token,
                'X-APP-Key: ' . BKASH_APP_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ]);
            $data = json_decode($resp['body'], true);
            if (($data['transactionStatus'] ?? '') === 'Completed') {
                return ['status' => 'paid', 'transaction_id' => $data['trxID'] ?? '', 'raw' => $data];
            }
            return ['status' => 'failed', 'message' => $data['statusMessage'] ?? 'Payment not completed', 'raw' => $data];
        } catch (Throwable $e) {
            log_app('bKash verify error: ' . $e->getMessage());
            return ['status' => 'failed', 'message' => 'Could not verify payment with bKash.'];
        }
    }

    private function getToken(): string
    {
        $resp = $this->http(BKASH_BASE_URL . '/tokenized/checkout/token/grant', [
            'app_key'    => BKASH_APP_KEY,
            'app_secret' => BKASH_APP_SECRET,
        ], 'POST', ['Content-Type: application/json', 'Accept: application/json']);
        $data = json_decode($resp['body'], true);
        if (empty($data['id_token'])) {
            throw new RuntimeException('bKash token grant failed: ' . ($data['statusMessage'] ?? 'unknown error'));
        }
        return $data['id_token'];
    }
}
