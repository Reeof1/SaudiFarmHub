<?php
/** @var array $bookings */
/** @var array $bookingCounts */
/** @var int $pendingFarms */
$bookingCounts = $bookingCounts ?? ['Pending' => 0, 'Approved' => 0, 'Cancelled' => 0, 'Completed' => 0];
$pendingFarms = (int)($pendingFarms ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-shield-lock"></i>
            <span>Admin dashboard</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">System overview</h1>
        <p class="fh-muted mb-0">Approve farms, monitor bookings, and review platform activity.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-success" href="<?= e(base_url('admin/users')) ?>">
            <i class="bi bi-people me-2"></i>Manage users
        </a>
        <a class="btn btn-outline-success" href="<?= e(base_url('admin/reports')) ?>">
            <i class="bi bi-file-earmark-text me-2"></i>Reports
        </a>
        <a class="btn btn-outline-success" href="<?= e(base_url('admin/activity')) ?>">
            <i class="bi bi-activity me-2"></i>System activity
        </a>
        <a class="btn btn-outline-success" href="<?= e(base_url('admin/alerts')) ?>">
            <i class="bi bi-bell me-2"></i>System alerts
        </a>
        <a class="btn btn-success" href="<?= e(base_url('admin/farms')) ?>">
            <i class="bi bi-check2-square me-2"></i>Farm approvals
            <?php if ($pendingFarms > 0): ?>
                <span class="badge text-bg-warning ms-2"><?= e((string)$pendingFarms) ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Pending farms</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$pendingFarms) ?></div>
                </div>
                <div class="icon-bubble bg-warning-subtle text-warning">
                    <i class="bi bi-building-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fh-card-soft p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small fh-muted">Bookings pending</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$bookingCounts['Pending']) ?></div>
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
                    <div class="small fh-muted">Bookings approved</div>
                    <div class="h4 fw-bold mb-0"><?= e((string)$bookingCounts['Approved']) ?></div>
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
                    <div class="h4 fw-bold mb-0"><?= e((string)$bookingCounts['Completed']) ?></div>
                </div>
                <div class="icon-bubble bg-info-subtle text-info">
                    <i class="bi bi-flag"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-5">
        <div class="fh-card-soft p-4 h-100">
            <h2 class="h6 fw-bold mb-3">Bookings status</h2>
            <canvas id="bookingStatusChart" height="220"></canvas>
            <div class="small fh-muted mt-3">
                This chart updates from the current booking status distribution.
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="fh-card-soft p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 fw-bold mb-0">Recent bookings</h2>
                <span class="fh-chip">Last <?= e((string)min(10, is_array($bookings ?? null) ? count($bookings) : 0)) ?></span>
            </div>

            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Farm</th>
                            <th>Activity</th>
                            <th>Date</th>
                            <th>Visitor</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $shown = 0; ?>
                        <?php foreach ($bookings as $b): ?>
                            <?php if ($shown >= 10) break; ?>
                            <?php
                            $status = (string)$b['status_name'];
                            $total = (float)$b['price'] * (int)$b['party_size'];
                            $shown++;
                            ?>
                            <tr>
                                <td><?= e((string)$b['farm_name']) ?></td>
                                <td><?= e((string)$b['activity_name']) ?></td>
                                <td><?= e((string)$b['schedule_date']) ?> <?= e((string)$b['start_time']) ?></td>
                                <td><?= e((string)$b['visitor_name']) ?></td>
                                <td><?= e($status) ?></td>
                                <td><?= e(number_format($total, 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="fh-muted">No bookings recorded yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const ctx = document.getElementById('bookingStatusChart');
        if (!ctx) return;
        const data = <?= json_encode($bookingCounts) ?>;
        const labels = Object.keys(data);
        const values = Object.values(data);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.85)',   // Pending
                        'rgba(25, 135, 84, 0.85)',   // Approved
                        'rgba(108, 117, 125, 0.70)', // Cancelled
                        'rgba(13, 110, 253, 0.75)'   // Completed
                    ],
                    borderColor: 'rgba(255,255,255,0.9)',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '65%'
            }
        });
    })();
</script>

