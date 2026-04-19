<?php
/** @var array $statusCounts */
/** @var int $unreadNotifications */
$statusCounts = $statusCounts ?? ['Pending' => 0, 'Approved' => 0, 'Cancelled' => 0, 'Completed' => 0];
$unreadNotifications = (int)($unreadNotifications ?? 0);
$favoritesCount = (int)($favoritesCount ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-person-check"></i>
            <span>Visitor dashboard</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">Welcome back</h1>
        <p class="fh-muted mb-0">Track bookings, get updates, and explore new farm experiences.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-success" href="<?= e(base_url('farms')) ?>"><i class="bi bi-search me-2"></i>Browse farms</a>
        <a class="btn btn-outline-success" href="<?= e(base_url('visitor/bookings')) ?>"><i class="bi bi-calendar2-check me-2"></i>My bookings</a>
        <a class="btn btn-outline-danger" href="<?= e(base_url('visitor/favorites')) ?>">
            <i class="bi bi-heart me-2"></i>My favorites
        </a>
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
                    <div class="small fh-muted">Pending</div>
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
                <div class="icon-bubble bg-success-subtle text-success">
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
                <div class="icon-bubble bg-info-subtle text-info">
                    <i class="bi bi-flag"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Cancelled</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$statusCounts['Cancelled']) ?></div>
                </div>
                <div class="icon-bubble bg-secondary-subtle text-secondary">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-8">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold mb-2">Quick tips</h2>
            <ul class="mb-0 fh-muted">
                <li>Pick an activity and date to see real-time available slots.</li>
                <li>Watch notifications for approvals and updates.</li>
                <li>Manage cancellations from “My bookings”.</li>
            </ul>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold mb-2">Next step</h2>
            <p class="fh-muted mb-3">Explore a farm and book a time slot that fits your schedule.</p>
            <a class="btn btn-success w-100" href="<?= e(base_url('farms')) ?>">Explore farms</a>
        </div>
    </div>
</div>

