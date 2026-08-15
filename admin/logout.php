<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
logout_admin();
flash('success', 'You have been logged out of the admin panel.');
redirect('admin/login.php');