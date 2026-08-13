<?php
declare(strict_types=1);

/**
 * Core helper functions shared across the application.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/images.php';
require_once INCLUDES_PATH . '/cart.php';

/**
 * Escape output for safe HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Build an absolute URL for the application.
 */
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/**
 * Build an asset URL.
 */
function asset(string $path): string
{
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

/**
 * Redirect to a location.
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Set a flash message.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Get and clear the flash message.
 */
function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/**
 * Old form input value (repopulation).
 */
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['old'][$key] ?? $default;
}

/**
 * Store old form input values.
 */
function with_old(array $data): void
{
    $_SESSION['old'] = $data;
}

/**
 * Format a monetary amount.
 */
function money(float $amount): string
{
    return CURRENCY . number_format($amount, $amount == floor($amount) ? 0 : 2);
}

/**
 * Validate a Bangladeshi phone number (01X-XXXXXXX).
 */
function is_valid_bd_phone(string $phone): bool
{
    $phone = trim($phone);
    if (preg_match('/^\+?8801[3-9]\d{8}$/', $phone)) {
        return true;
    }
    return (bool) preg_match('/^01[3-9]\d{8}$/', $phone);
}

/**
 * Normalize a Bangladeshi phone number to the 01XXXXXXXXX format.
 */
function normalize_bd_phone(string $phone): string
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($phone, '880')) {
        $phone = '0' . substr($phone, 3);
    }
    return $phone;
}

/**
 * Generate a slug from a string.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Truncate a string to a given length.
 */
function truncate(string $text, int $length = 120, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length)) . $suffix;
}

/**
 * Paginate helper.
 */
function paginate(int $total, int $perPage = 12): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    return [
        'page'       => $page,
        'perPage'    => $perPage,
        'total'      => $total,
        'totalPages' => $totalPages,
        'offset'     => ($page - 1) * $perPage,
    ];
}

/**
 * Build a pagination-friendly query string, preserving current filters.
 */
function pagination_query(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    unset($params['page']);
    return http_build_query($params);
}

/**
 * Return a JSON response and exit.
 */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Check if the current request is AJAX.
 */
function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Read application settings from the database (cached per request).
 */
function settings(string $key = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (fetch_all('SELECT setting_key, setting_value FROM settings') as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    if ($key === null) {
        return $cache;
    }
    return $cache[$key] ?? null;
}

/**
 * Get the active delivery zones ordered for dropdowns.
 */
function delivery_zones(): array
{
    return fetch_all('SELECT * FROM delivery_zones WHERE is_active = 1 ORDER BY sort_order ASC, district ASC');
}

/**
 * Get delivery fee for a given district name.
 */
function delivery_fee_for(string $district): float
{
    $zone = fetch_one(
        'SELECT * FROM delivery_zones WHERE district = ? AND is_active = 1 LIMIT 1',
        [$district]
    );
    if ($zone) {
        return (float) $zone['delivery_fee'];
    }
    // Fallback zone (e.g. "Other Districts")
    $fallback = fetch_one(
        "SELECT * FROM delivery_zones WHERE district = 'Other Districts' AND is_active = 1 LIMIT 1"
    );
    return $fallback ? (float) $fallback['delivery_fee'] : 0.0;
}

/**
 * Check if a cart total qualifies for free delivery in a zone.
 */
function free_delivery_threshold_for(string $district): float
{
    $zone = fetch_one(
        'SELECT * FROM delivery_zones WHERE district = ? AND is_active = 1 LIMIT 1',
        [$district]
    );
    return $zone ? (float) $zone['free_delivery_threshold'] : 0.0;
}

/**
 * List of Bangladesh divisions.
 */
function bd_divisions(): array
{
    return [
        'Dhaka', 'Chattogram', 'Rajshahi', 'Khulna',
        'Barishal', 'Sylhet', 'Rangpur', 'Mymensingh',
    ];
}

/**
 * Sanitize a posted value.
 */
function input(string $key, mixed $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (is_array($value)) {
        return $default;
    }
    return trim((string) $value);
}

/**
 * Log a message to the application log file.
 */
function log_app(string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents(LOG_PATH . '/app.log', $line, FILE_APPEND);
}
