<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name    = trim((string) input('name'));
    $email   = trim((string) input('email'));
    $phone   = normalize_bd_phone(input('phone'));
    $subject = trim((string) input('subject'));
    $message = trim((string) input('message'));

    if ($name === '' || mb_strlen($name) < 3) $errors['name'] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';
    if ($phone !== '' && !is_valid_bd_phone($phone)) $errors['phone'] = 'Please enter a valid Bangladeshi phone number.';
    if ($message === '' || mb_strlen($message) < 10) $errors['message'] = 'Please write a message of at least 10 characters.';

    if (!$errors) {
        query('INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)', [$name, $email, $phone, $subject, $message]);
        $success = true;
    }
    with_old(['name' => $name, 'email' => $email, 'phone' => $phone, 'subject' => $subject, 'message' => $message]);
}

$page_title = 'Contact Us';
$page_meta  = 'Get in touch with Lichi Lover — we are happy to answer your questions.';
$page_canonical = url('contact.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="page-hero">
    <h1>Contact Us</h1>
    <p>Questions about your order, delivery or the season? We're here to help.</p>
</div>

<section class="section">
    <div class="container" style="max-width:920px">
        <div class="account-layout" style="grid-template-columns:1fr">
            <div class="checkout-form-card">
                <h3 style="margin-bottom:18px">Send Us a Message</h3>

                <?php if ($success): ?>
                <div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>Thank you! Your message has been sent. We'll get back to you soon.</span></div>
                <?php endif; ?>
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><i data-lucide="info"></i><span><?= e($flash['message']) ?></span></div>
                <?php endif; ?>

                <form method="post" action="<?= url('contact.php') ?>" data-loading>
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="form-field <?= isset($errors['name']) ? 'has-error' : '' ?>">
                            <label>Your Name <span class="req">*</span></label>
                            <input type="text" name="name" value="<?= e(old('name')) ?>" required>
                            <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['email']) ? 'has-error' : '' ?>">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" name="email" value="<?= e(old('email')) ?>" required>
                            <?php if (isset($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['phone']) ? 'has-error' : '' ?>">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?= e(old('phone')) ?>" placeholder="017XXXXXXXX">
                            <?php if (isset($errors['phone'])): ?><span class="form-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field">
                            <label>Subject</label>
                            <input type="text" name="subject" value="<?= e(old('subject')) ?>" placeholder="e.g. Delivery question">
                        </div>
                        <div class="form-field full <?= isset($errors['message']) ? 'has-error' : '' ?>">
                            <label>Message <span class="req">*</span></label>
                            <textarea name="message" rows="5" required><?= e(old('message')) ?></textarea>
                            <?php if (isset($errors['message'])): ?><span class="form-error"><?= e($errors['message']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="margin-top:16px">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>