<?php
declare(strict_types=1);

/**
 * Cash on Delivery payment method.
 */

require_once __DIR__ . '/gateway.php';

class CodGateway extends PaymentGateway
{
    public function name(): string
    {
        return 'Cash on Delivery';
    }

    public function methodKey(): string
    {
        return 'cod';
    }

    public function initiate(array $order, string $ip = ''): array
    {
        // COD is paid at the door; no online initiation happens.
        return [
            'status'    => 'pending',
            'reference' => 'COD-' . $order['order_number'],
            'message'   => 'Pay in cash when your order is delivered.',
        ];
    }

    public function verify(array $payment, array $request = []): array
    {
        // COD is confirmed by delivery staff, not an online gateway.
        return ['status' => 'pending', 'message' => 'COD payments are settled on delivery.'];
    }
}
