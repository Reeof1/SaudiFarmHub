<?php
/** @var int $farmCount */
/** @var array $statusCounts */
/** @var int $unreadNotifications */
$farmCount = (int)($farmCount ?? 0);
$statusCounts = $statusCounts ?? ['Pending' => 0, 'Approved' => 0, 'Cancelled' => 0, 'Completed' => 0];
$unreadNotifications = (int)($unreadNotifications ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-house-gear"></i>
            <span>Owner dashboard</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">Operations overview</h1>
        <p class="fh-muted mb-0">Manage farms, publish schedules, and respond to bookings quickly.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-success" href="<?= e(base_url('owner/farms')) ?>"><i class="bi bi-sliders me-2"></i>Manage farms</a>
        <a class="btn btn-outline-success" href="<?= e(base_url('dashboard/owner/bookings')) ?>"><i class="bi bi-inboxes me-2"></i>Bookings</a>
        <a class="btn btn-outline-success" href="<?= e(base_url('notifications')) ?>">
            <i class="bi bi-bell me-2"></i>Notifications
            <?php if ($unreadNotifications > 0): ?>
                <span class="badge text-bg-warning ms-2"><?= e((string)$unreadNotifications) ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Farms</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$farmCount) ?></div>
                </div>
                <div class="icon-bubble bg-success-subtle text-success">
                    <i class="bi bi-tree"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Pending bookings</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$statusCounts['Pending']) ?></div>
                </div>
                <div class="icon-bubble bg-warning-subtle text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Approved</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$statusCounts['Approved']) ?></div>
                </div>
                <div class="icon-bubble bg-info-subtle text-info">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Completed</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$statusCounts['Completed']) ?></div>
                </div>
                <div class="icon-bubble bg-primary-subtle text-primary">
                    <i class="bi bi-flag"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-8">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold mb-2">Recommended workflow</h2>
            <ol class="mb-0 fh-muted">
                <li>Create your farm(s).</li>
                <li>Add activities for each farm.</li>
                <li>Publish schedules (date/time/capacity/price).</li>
                <li>Approve and complete bookings from the Bookings panel.</li>
            </ol>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold mb-2">Quick actions</h2>
            <div class="d-grid gap-2">
                <a class="btn btn-success" href="<?= e(base_url('owner/farm/create')) ?>"><i class="bi bi-plus-circle me-2"></i>Add a farm</a>
                <a class="btn btn-outline-success" href="<?= e(base_url('owner/farms')) ?>"><i class="bi bi-pencil-square me-2"></i>Edit farms</a>
                <a class="btn btn-outline-success" href="<?= e(base_url('dashboard/owner/bookings')) ?>"><i class="bi bi-inboxes me-2"></i>Review bookings</a>
            </div>
        </div>
    </div>
</div>

