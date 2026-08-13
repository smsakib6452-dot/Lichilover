<?php
declare(strict_types=1);

/**
 * Lichi Lover — Application configuration bootstrap.
 * Loads .env, defines constants, sets error handling and sessions.
 */

// ---------------------------------------------------------------------------
// Minimal .env loader (no external dependencies)
// ---------------------------------------------------------------------------
if (!function_exists('load_env_file')) {
    function load_env_file(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            if ($key === '' || getenv($key) !== false) {
                continue;
            }
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

load_env_file(dirname(__DIR__) . '/.env');
load_env_file(dirname(__DIR__) . '/config/local.php');

// ---------------------------------------------------------------------------
// Environment
// ---------------------------------------------------------------------------
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_NAME', getenv('APP_NAME') ?: 'Lichi Lover');
define('APP_TAGLINE', getenv('APP_TAGLINE') ?: 'Freshness You Can Taste.');
define('APP_VERSION', '1.0.0');

define('APP_DEBUG', APP_ENV === 'development');

// ---------------------------------------------------------------------------
// Paths
// ---------------------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('LOG_PATH', ROOT_PATH . '/logs');

// ---------------------------------------------------------------------------
// Error handling
// ---------------------------------------------------------------------------
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . '/php-errors.log');

if (!function_exists('app_error_handler')) {
    function app_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $msg = sprintf('[%s] %s in %s:%d', $errno, $errstr, $errfile, $errline);
        error_log($msg . PHP_EOL, 3, LOG_PATH . '/php-errors.log');
        if (APP_DEBUG) {
            echo '<div style="background:#fff5f5;border:1px solid #f00;padding:8px;margin:8px;color:#900;font-family:monospace;font-size:13px;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        return true;
    }
}
set_error_handler('app_error_handler');

if (!function_exists('app_exception_handler')) {
    function app_exception_handler(Throwable $e): void
    {
        error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL, 3, LOG_PATH . '/php-errors.log');
        http_response_code(500);
        if (APP_DEBUG) {
            echo '<div style="background:#fff5f5;border:1px solid #f00;padding:8px;margin:8px;color:#900;font-family:monospace;font-size:13px;">';
            echo 'Exception: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '<br>';
            echo htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ':' . $e->getLine();
            echo '</div>';
        } else {
            echo 'Something went wrong. Please try again later.';
        }
    }
}
set_exception_handler('app_exception_handler');

// ---------------------------------------------------------------------------
// Database
// ---------------------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'lichi_lover');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// ---------------------------------------------------------------------------
// Base URL — auto-detected, overridable via .env
// ---------------------------------------------------------------------------
if (!function_exists('detect_base_url')) {
    function detect_base_url(): string
    {
        $override = getenv('BASE_URL');
        if ($override) {
            return rtrim($override, '/') . '/';
        }
        $docRoot  = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
        $appRoot  = str_replace('\\', '/', realpath(ROOT_PATH));
        if ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) {
            $base = substr($appRoot, strlen($docRoot));
            return rtrim($base, '/') . '/';
        }
        return '/';
    }
}
define('BASE_URL', detect_base_url());

// ---------------------------------------------------------------------------
// Payments
// ---------------------------------------------------------------------------
define('PAYMENT_MODE', getenv('PAYMENT_MODE') ?: 'demo'); // demo | live

define('BKASH_APP_KEY', getenv('BKASH_APP_KEY') ?: '');
define('BKASH_APP_SECRET', getenv('BKASH_APP_SECRET') ?: '');
define('BKASH_USERNAME', getenv('BKASH_USERNAME') ?: '');
define('BKASH_PASSWORD', getenv('BKASH_PASSWORD') ?: '');
define('BKASH_BASE_URL', getenv('BKASH_BASE_URL') ?: 'https://tokenized.sandbox.bka.sh/v1.2.0-beta');

define('NAGAD_MERCHANT_ID', getenv('NAGAD_MERCHANT_ID') ?: '');
define('NAGAD_MERCHANT_NUMBER', getenv('NAGAD_MERCHANT_NUMBER') ?: '');
define('NAGAD_PUBLIC_KEY', getenv('NAGAD_PUBLIC_KEY') ?: '');
define('NAGAD_PRIVATE_KEY', getenv('NAGAD_PRIVATE_KEY') ?: '');
define('NAGAD_BASE_URL', getenv('NAGAD_BASE_URL') ?: 'https://sandbox.mynagad.com');

// ---------------------------------------------------------------------------
// Business / contact placeholders (configurable, no real data)
// ---------------------------------------------------------------------------
define('SHOP_EMAIL', getenv('SHOP_EMAIL') ?: 'hello@lichilover.example');
define('SHOP_PHONE', getenv('SHOP_PHONE') ?: '');
define('SHOP_ADDRESS', getenv('SHOP_ADDRESS') ?: '');
define('WHATSAPP_NUMBER', getenv('WHATSAPP_NUMBER') ?: '');

// ---------------------------------------------------------------------------
// Currency / regional
// ---------------------------------------------------------------------------
define('CURRENCY', getenv('CURRENCY') ?: '৳');
define('COUNTRY_CODE', 'BD');

// ---------------------------------------------------------------------------
// Sessions (secure by default)
// ---------------------------------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('LLSESSID');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// ---------------------------------------------------------------------------
// Timezone
// ---------------------------------------------------------------------------
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Dhaka');
