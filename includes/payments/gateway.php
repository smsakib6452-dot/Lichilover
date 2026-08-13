<?php
declare(strict_types=1);

/**
 * Payment gateway architecture.
 *
 * Every gateway implements the same interface. In PAYMENT_MODE=demo the
 * gateways return a simulated flow and record gateway_response JSON marked
 * with "demo": true — no real transaction is ever claimed to have occurred.
 * In PAYMENT_MODE=live the gateways call the real gateway APIs and only mark
 * a payment paid after server-side verification.
 *
 * A payment is NEVER marked "paid" merely because the browser returned from
 * the payment page; verification must happen server-side (see verify()).
 */

abstract class PaymentGateway
{
    protected string $mode;

    public function __construct()
    {
        $this->mode = PAYMENT_MODE;
    }

    public function isDemo(): bool
    {
        return $this->mode === 'demo';
    }

    abstract public function name(): string;

    abstract public function methodKey(): string;

    /**
     * Begin a payment for an order.
     *
     * @param array $order   Full order row (with order_number, total).
     * @param string $ip     Customer IP address.
     * @return array{status:string, redirect?:string, demo?:bool, message?:string, reference?:string}
     */
    abstract public function initiate(array $order, string $ip = ''): array;

    /**
     * Verify a previously initiated payment server-side.
     * Must be called by gateway callbacks / IPN endpoints, never trusted from the browser.
     *
     * @return array{status:string, transaction_id?:string, message?:string}
     */
    abstract public function verify(array $payment, array $request = []): array;

    /**
     * Build an http_build_query-style signature helper.
     */
    protected function http(string $url, array $payload = null, string $method = 'GET', array $headers = [], int $timeout = 20): array
    {
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        if ($payload !== null) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = is_array($payload) ? json_encode($payload) : $payload;
        }
        if ($method === 'GET' && $payload !== null) {
            $options[CURLOPT_POST] = false;
            $options[CURLOPT_HTTPGET] = true;
        }
        if ($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return ['code' => $code, 'body' => $body, 'error' => $error];
    }
}

/**
 * Create the gateway for a payment method.
 */
function payment_gateway(string $method): PaymentGateway
{
    $method = strtolower($method);
    return match ($method) {
        'bkash' => new BkashGateway(),
        'nagad' => new NagadGateway(),
        'cod'   => new CodGateway(),
        default => throw new InvalidArgumentException('Unsupported payment method: ' . $method),
    };
}

/**
 * Persist a payment row for an order.
 */
function create_payment(array $order, string $method, string $reference = ''): int
{
    $stmt = query(
        'INSERT INTO payments (order_id, method, payment_id, amount, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
        [
            $order['id'],
            $method,
            $reference ?: 'PAY-' . strtoupper(bin2hex(random_bytes(6))),
            $order['total'],
            'pending',
        ]
    );
    return (int) db()->lastInsertId();
}

/**
 * Update an existing payment row.
 */
function update_payment(int $paymentId, array $fields): void
{
    $set = [];
    $params = [];
    $allowed = ['status', 'transaction_id', 'gateway_response', 'payment_id'];
    foreach ($fields as $key => $value) {
        if (in_array($key, $allowed, true)) {
            $set[] = "`$key` = ?";
            $params[] = $value;
        }
    }
    if (!$set) {
        return;
    }
    $set[] = 'updated_at = NOW()';
    $params[] = $paymentId;
    query('UPDATE payments SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
}

/**
 * Fetch the payment record for an order + method.
 */
function order_payment(int $orderId, string $method): ?array
{
    return fetch_one('SELECT * FROM payments WHERE order_id = ? AND method = ? ORDER BY id DESC LIMIT 1', [$orderId, $method]);
}

/**
 * Allowed payment statuses.
 */
function payment_statuses(): array
{
    return ['pending', 'processing', 'paid', 'failed', 'refunded'];
}
