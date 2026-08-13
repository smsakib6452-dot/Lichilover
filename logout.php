<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_login();
logout_user();
flash('success', 'You have been logged out. See you soon!');
redirect('index.php');