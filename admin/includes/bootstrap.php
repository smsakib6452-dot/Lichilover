<?php
declare(strict_types=1);

/**
 * Admin bootstrap. Require on every admin page.
 * Redirects to login if not authenticated.
 */

require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/payments/bkash.php';
require_once dirname(__DIR__, 2) . '/includes/payments/nagad.php';
require_once dirname(__DIR__, 2) . '/includes/payments/cod.php';

require_admin();
