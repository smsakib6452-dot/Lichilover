<?php
declare(strict_types=1);

/**
 * Authentication helpers for customers and admins.
 */

/**
 * Return the currently logged-in customer (array) or null.
 */
function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user ?: null;
    }
    if (empty($_SESSION['user_id'])) {
        $user = null;
        return null;
    }
    $row = fetch_one('SELECT * FROM users WHERE id = ? AND is_active = 1', [$_SESSION['user_id']]);
    $user = $row ?: null;
    return $user;
}

/**
 * Return the currently logged-in admin (array) or null.
 */
function current_admin(): ?array
{
    static $admin = false;
    if ($admin !== false) {
        return $admin ?: null;
    }
    if (empty($_SESSION['admin_id'])) {
        $admin = null;
        return null;
    }
    $row = fetch_one('SELECT * FROM admins WHERE id = ? AND is_active = 1', [$_SESSION['admin_id']]);
    $admin = $row ?: null;
    return $admin;
}

/**
 * True if a customer is logged in.
 */
function is_logged_in(): bool
{
    return current_user() !== null;
}

/**
 * True if an admin is logged in.
 */
function is_admin_logged_in(): bool
{
    return current_admin() !== null;
}

/**
 * Require a logged-in customer; otherwise redirect to login.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('info', 'Please login to continue.');
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? url('/');
        redirect('login.php');
    }
}

/**
 * Require a logged-in admin; otherwise redirect to admin login.
 */
function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect('admin/login.php');
    }
}

/**
 * Log in a customer.
 */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['cart']);
}

/**
 * Log out the current customer.
 */
function logout_user(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

/**
 * Log in an admin.
 */
function login_admin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
}

/**
 * Log out the current admin.
 */
function logout_admin(): void
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}
