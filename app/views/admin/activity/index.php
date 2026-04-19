<?php
/** @var array $events */
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-activity"></i>
            <span>System activity monitoring</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">System Activity</h1>
        <p class="fh-muted mb-0">Recent platform actions across users, farms, bookings, alerts, and reports.</p>
    </div>
    <a class="btn btn-outline-success" href="<?= e(base_url('dashboard/admin')) ?>">Back to Admin Dashboard</a>
</div>

<?php if (!empty($events)): ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Time</th>
                <th>Type</th>
                <th>Event</th>
                <th>Actor</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><?= e((string)$e['event_time']) ?></td>
                    <td><span class="badge text-bg-light"><?= e((string)$e['event_type']) ?></span></td>
                    <td><?= e((string)$e['event_label']) ?></td>
                    <td><?= e((string)$e['actor']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">No activity found.</div>
<?php endif; ?>

