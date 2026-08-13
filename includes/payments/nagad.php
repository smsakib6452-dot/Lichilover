<?php
declare(strict_types=1);

/**
 * Nagad payment gateway.
 *
 * Demo mode: returns a simulated flow clearly labelled "demo".
 * Live mode: uses the Nagad Initialize + Verify API flow.
 */

require_once __DIR__ . '/gateway.php';

class NagadGateway extends PaymentGateway
{
    public function name(): string
    {
        return 'Nagad';
    }

    public function methodKey(): string
    {
        return 'nagad';
    }

    public function initiate(array $order, string $ip = ''): array
    {
        if ($this->isDemo()) {
            return [
                'status'    => 'pending',
                'demo'      => true,
                'reference' => 'DEMO-NAGAD-' . $order['order_number'],
                'message'   => 'Demo payment mode — no real money will be charged.',
            ];
        }

        try {
            $timestamp = date('YmdHis');
            $merchantId = NAGAD_MERCHANT_ID;
            $orderNumber = $order['order_number'];
            $amount = number_format((float) $order['total'], 2, '.', '');
            $ipnUrl = url('payment-callback.php?method=nagad&payment=' . ($order['payment_id'] ?? '') . '&order=' . urlencode($orderNumber));

            $sensitive = json_encode([
                'merchantId' => $merchantId,
                'datetime'   => $timestamp,
                'orderId'    => $orderNumber,
                'challenge'  => $this->generateChallenge(),
            ]);
            $publicKey = $this->getPublicKey();
            $encryptedSensitive = $this->rsaEncrypt($sensitive, $publicKey);
            $signatureData = $merchantId . $orderNumber . $amount . $timestamp . $encryptedSensitive;
            $signature = $this->rsaSign($signatureData);

            $body = [
                'accountNumber' => NAGAD_MERCHANT_NUMBER,
                'dateTime'      => $timestamp,
                'sensitiveData' => $this->base64UrlEncode($encryptedSensitive),
                'signature'     => $this->base64UrlEncode($signature),
            ];

            $resp = $this->http(NAGAD_BASE_URL . '/api/dfs/check-out/initialize/' . $merchantId . '/' . $orderNumber, $body, 'POST', [
                'Content-Type: application/json',
                'X-KM-Api-Version: v-0.2.0',
                'X-KM-IP-V4: ' . $ip,
                'X-KM-Client-Type: PC_WEB',
                'X-KM-Api-Key: ' . NAGAD_PUBLIC_KEY,
            ]);

            $data = json_decode($resp['body'], true);
            if (!empty($data['callBackUrl'])) {
                return [
                    'status'    => 'processing',
                    'redirect'  => $data['callBackUrl'] . '?paymentReferenceId=' . urlencode($data['paymentReferenceId'] ?? ''),
                    'reference' => $data['paymentReferenceId'] ?? ('NAGAD-' . $orderNumber),
                ];
            }
            return ['status' => 'failed', 'message' => $data['message'] ?? 'Nagad gateway error'];
        } catch (Throwable $e) {
            log_app('Nagad initiate error: ' . $e->getMessage());
            return ['status' => 'failed', 'message' => 'Could not reach Nagad. Please try again or choose another method.'];
        }
    }

    public function verify(array $payment, array $request = []): array
    {
        if ($this->isDemo()) {
            $reference = $payment['payment_id'] ?? '';
            return [
                'status'         => 'paid',
                'transaction_id' => $reference,
                'message'        => 'Demo Nagad transaction simulated. No real money was charged.',
            ];
        }

        try {
            $reference = $request['paymentReferenceId'] ?? $payment['payment_id'] ?? '';
            if (!$reference) {
                return ['status' => 'failed', 'message' => 'Missing payment reference'];
            }
            $resp = $this->http(NAGAD_BASE_URL . '/api/dfs/verify/payment/' . $reference, null, 'GET', [
                'Content-Type: application/json',
                'X-KM-Api-Version: v-0.2.0',
            ]);
            $data = json_decode($resp['body'], true);
            if (($data['paymentStatus'] ?? '') === 'Completed') {
                return ['status' => 'paid', 'transaction_id' => $data['issuerPaymentReferenceNo'] ?? $reference, 'raw' => $data];
            }
            return ['status' => 'failed', 'message' => $data['message'] ?? 'Payment not completed', 'raw' => $data];
        } catch (Throwable $e) {
            log_app('Nagad verify error: ' . $e->getMessage());
            return ['status' => 'failed', 'message' => 'Could not verify payment with Nagad.'];
        }
    }

    private function generateChallenge(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function getPublicKey(): string
    {
        $resp = $this->http(NAGAD_BASE_URL . '/api/dfs/security/publickey', null, 'GET');
        $data = json_decode($resp['body'], true);
        if (empty($data['publicKey'])) {
            throw new RuntimeException('Nagad public key fetch failed');
        }
        $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($data['publicKey'], 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        return $key;
    }

    private function rsaEncrypt(string $data, string $publicKey): string
    {
        $publicKey = openssl_pkey_get_public($publicKey);
        if (!$publicKey) {
            throw new RuntimeException('Invalid Nagad public key');
        }
        if (!openssl_public_encrypt($data, $encrypted, $publicKey)) {
            throw new RuntimeException('Nagad encryption failed');
        }
        return $encrypted;
    }

    private function rsaSign(string $data): string
    {
        $privateKey = openssl_pkey_get_private("-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap(NAGAD_PRIVATE_KEY, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----");
        if (!$privateKey) {
            throw new RuntimeException('Invalid Nagad private key');
        }
        if (!openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Nagad signing failed');
        }
        return $signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
