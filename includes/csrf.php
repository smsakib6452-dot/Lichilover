<?php
declare(strict_types=1);

/**
 * CSRF protection helpers.
 */

/**
 * Generate (or reuse) a CSRF token for the current session.
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF field for forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(generate_csrf_token()) . '">';
}

/**
 * Verify a submitted CSRF token against the session token.
 */
function verify_csrf_token(?string $token = null): bool
{
    $token = $token ?? ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF and abort with a friendly error on failure.
 */
function require_csrf(): void
{
    if (!verify_csrf_token()) {
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Invalid security token. Please reload the page and try again.'], 419);
        }
        http_response_code(419);
        flash('error', 'Your session expired. Please try again.');
        redirect($_SERVER['REQUEST_URI'] ?? '/');
    }
}
