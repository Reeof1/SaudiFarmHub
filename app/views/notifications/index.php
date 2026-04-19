<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-bell"></i>
            <span>Updates & alerts</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-1 fh-section-title">Notifications</h1>
        <p class="fh-muted mb-0">Booking updates and alerts will appear here.</p>
    </div>
    <button id="mark-read" class="btn btn-outline-success btn-sm">Mark all as read</button>
</div>

<?php if (!empty($notifications)): ?>
    <div class="list-group fh-card-soft p-2">
        <?php foreach ($notifications as $n): ?>
            <?php $isRead = ((int)($n['is_read'] ?? 0) === 1); ?>
            <div class="list-group-item d-flex flex-column gap-1 border-0 rounded-4 mb-2 <?= $isRead ? '' : 'border border-success' ?>">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="fw-semibold">
                        <?= e((string)($n['type'] ?? 'booking')) ?>
                    </div>
                    <?php if (!$isRead): ?>
                        <span class="badge text-bg-success">Unread</span>
                    <?php endif; ?>
                </div>
                <div><?= e((string)($n['message'] ?? '')) ?></div>
                <div class="fh-muted small">
                    <?= e((string)($n['created_at'] ?? '')) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">
        No notifications yet.
    </div>
<?php endif; ?>

<script>
    document.getElementById('mark-read')?.addEventListener('click', async () => {
        const csrf = <?= json_encode(csrf_token()) ?>;
        await fetch('<?= e(base_url('notifications/mark-read')) ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({csrf_token: csrf})
        });
        location.reload();
    });
</script>

