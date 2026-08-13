<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$faqs = [
    [
        'q' => 'How fresh is the lichi?',
        'a' => 'Our lichi is harvested at the peak of the season from trusted orchards and delivered within hours of harvest. Every fruit is quality-checked before packing, so what reaches your door is as fresh as it gets.',
    ],
    [
        'q' => 'How can I order?',
        'a' => 'Simply browse the shop, choose your favourite lichi and size, add it to your cart and check out. You can pay with bKash, Nagad or cash on delivery. You can also order without creating an account.',
    ],
    [
        'q' => 'Which areas do you deliver to?',
        'a' => 'We deliver to all 64 districts of Bangladesh. Delivery charges are calculated at checkout based on your district, and free delivery is available for eligible orders.',
    ],
    [
        'q' => 'What payment methods are available?',
        'a' => 'We accept bKash, Nagad and Cash on Delivery. Your payment details are kept secure and are never shared.',
    ],
    [
        'q' => 'Can I cancel my order?',
        'a' => 'Yes. Orders can be cancelled before they are shipped. Please contact us as soon as possible after placing your order and we will help you cancel it and arrange a refund where applicable.',
    ],
    [
        'q' => 'How should I store lichi?',
        'a' => 'Keep lichi in a cool, dry place. For the best taste, refrigerate and consume within 2 days. Wash gently just before eating. Store in the fridge for up to 3 days.',
    ],
];

$page_title = 'Frequently Asked Questions';
$page_meta  = 'Answers to common questions about ordering fresh lichi from Lichi Lover.';
$page_canonical = url('faq.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="page-hero">
    <h1>Frequently Asked Questions</h1>
    <p>Everything you need to know about ordering fresh lichi.</p>
</div>

<section class="section">
    <div class="container" style="max-width:760px">
        <?php foreach ($faqs as $faq): ?>
        <div class="faq-item">
            <button class="faq-q" type="button">
                <?= e($faq['q']) ?>
                <i data-lucide="chevron-down"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner"><?= e($faq['a']) ?></div></div>
        </div>
        <?php endforeach; ?>

        <div style="text-align:center;margin-top:40px">
            <p style="color:var(--muted)">Still have questions?</p>
            <a href="<?= url('contact.php') ?>" class="btn btn-primary" style="margin-top:10px">Contact Us</a>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>