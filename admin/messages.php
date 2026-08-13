<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');
    $id = (int) input('message_id');

    if ($action === 'mark_read') {
        query('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
        flash('success', 'Marked as read.');
    }
    if ($action === 'delete') {
        query('DELETE FROM contact_messages WHERE id = ?', [$id]);
        flash('success', 'Message deleted.');
    }
    redirect('messages.php');
}

$filter = $_GET['filter'] ?? 'unread';
if ($filter === 'unread') {
    $messages = fetch_all('SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY id DESC LIMIT 100');
} else {
    $messages = fetch_all('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 100');
}

$unread = (int) fetch_val('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');

$adminTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <form method="get" action="<?= url('admin/messages.php') ?>" style="display:flex;gap:8px">
        <select name="filter" onchange="this.form.submit()">
            <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Unread (<?= $unread ?>)</option>
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
        </select>
    </form>
</div>

<div class="admin-card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                <tr>
                    <td><strong><?= e($m['name']) ?></strong></td>
                    <td>
                        <div><?= e($m['email']) ?></div>
                        <div style="font-size:12px;color:var(--muted)"><?= e($m['phone'] ?: '—') ?></div>
                    </td>
                    <td><?= e($m['subject'] ?: '—') ?></td>
                    <td style="max-width:320px"><?= e(truncate($m['message'], 140)) ?></td>
                    <td><?= e(date('M j, Y h:i A', strtotime($m['created_at']))) ?></td>
                    <td><span class="badge <?= (int) $m['is_read'] === 1 ? 'badge-active' : 'badge-pending' ?>"><?= (int) $m['is_read'] === 1 ? 'Read' : 'Unread' ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if (!(int) $m['is_read']): ?>
                            <form method="post" action="<?= url('admin/messages.php') ?>" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm">Mark Read</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('admin/messages.php') ?>" style="display:inline" data-confirm="Delete this message?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$messages): ?>
                <tr><td colspan="7" class="empty-state">No messages.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>