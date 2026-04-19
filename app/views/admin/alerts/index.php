<?php
/** @var array $alerts */
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-bell"></i>
            <span>System alerts</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">Admin Alerts</h1>
        <p class="fh-muted mb-0">Critical and informational alerts delivered to administrator account.</p>
    </div>
    <a class="btn btn-outline-success" href="<?= e(base_url('dashboard/admin')) ?>">Back to Admin Dashboard</a>
</div>

<?php if (!empty($alerts)): ?>
    <div class="list-group fh-card-soft p-2">
        <?php foreach ($alerts as $a): ?>
            <div class="list-group-item border-0 rounded-4 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><?= e((string)$a['type']) ?></span>
                    <?php if ((int)$a['is_read'] === 0): ?>
                        <span class="badge text-bg-warning">Unread</span>
                    <?php endif; ?>
                </div>
                <div class="mt-1"><?= e((string)$a['message']) ?></div>
                <div class="small fh-muted mt-1"><?= e((string)$a['created_at']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">No alerts found for this admin account.</div>
<?php endif; ?>

