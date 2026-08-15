<?php
// One-time helper: reset the admin password for the store admin account.
// Run once (http://localhost/lichi-lover/update_admin_password.php) then delete this file.
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lichi_lover;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$email = 'admin@lichi-lover.com';
$passwordHash = password_hash('admin123', PASSWORD_BCRYPT);

$stmt = $pdo->prepare('UPDATE admins SET password = ?, must_change_password = 1 WHERE email = ?');
$stmt->execute([$passwordHash, $email]);

echo "Admin password updated successfully!\n";
echo "Email: $email\n";
echo "Password: admin123\n";
echo "Please change it from Admin > Settings > Change Password.\n";
