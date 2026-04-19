<?php
/** @var array $farm */
/** @var array $activities */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-list-stars"></i>
            <span>Activity manager</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-1 fh-section-title">Activities for <?= e((string)$farm['name']) ?></h1>
        <p class="fh-muted mb-0">Define experiences visitors can book at this farm.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= e(base_url('owner/farms')) ?>">Back to Farms</a>
        <a class="btn btn-success" href="<?= e(base_url('owner/farm/activity/create?farm_id=' . (int)$farm['id'])) ?>">Add Activity</a>
    </div>
</div>

<?php if (!empty($activities)): ?>
    <div class="list-group fh-card-soft p-2">
        <?php foreach ($activities as $a): ?>
            <div class="list-group-item border-0 rounded-4 mb-2 d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-semibold text-success"><?= e((string)$a['name']) ?></div>
                    <div class="small fh-muted mb-1">
                        Type: <?= e((string)$a['activity_type']) ?>
                    </div>
                    <div class="small fh-muted">
                        <?= e(mb_substr((string)$a['description'], 0, 140)) ?>...
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    <a class="btn btn-outline-success btn-sm"
                       href="<?= e(base_url('owner/farm/schedules?activity_id=' . (int)$a['id'])) ?>">
                        Manage Schedules
                    </a>
                    <a class="btn btn-outline-primary btn-sm"
                       href="<?= e(base_url('owner/farm/activity/edit?id=' . (int)$a['id'])) ?>">
                        Edit
                    </a>
                    <form method="post" action="<?= e(base_url('owner/farm/activity/delete')) ?>"
                          onsubmit="return confirm('Delete this activity? Existing bookings will remain.');">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= e((string)$a['id']) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">
        No activities yet. Click <strong>Add Activity</strong> to create your first one.
    </div>
<?php endif; ?>

