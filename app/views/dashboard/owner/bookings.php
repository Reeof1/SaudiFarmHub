<?php
/** @var array $bookings */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-inboxes"></i>
            <span>Booking operations</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-1 fh-section-title">Bookings for Your Farms</h1>
        <p class="fh-muted mb-0">Approve, complete, or cancel bookings made on your activities.</p>
    </div>
    <a href="<?= e(base_url('dashboard/owner')) ?>" class="btn btn-outline-secondary">Back to Owner Dashboard</a>
</div>

<?php if (!empty($bookings)): ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Farm</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Time</th>
                <th>Party</th>
                <th>Status</th>
                <th>Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <?php
                $status = (string)$b['status_name'];
                $canApprove = ($status === 'Pending');
                $canComplete = ($status === 'Approved');
                $canCancel = in_array($status, ['Pending', 'Approved'], true);
                $total = (float)$b['price'] * (int)$b['party_size'];
                ?>
                <tr>
                    <td><?= e((string)$b['farm_name']) ?></td>
                    <td><?= e((string)$b['activity_name']) ?></td>
                    <td><?= e((string)$b['schedule_date']) ?></td>
                    <td><?= e((string)$b['start_time']) ?> – <?= e((string)$b['end_time']) ?></td>
                    <td><?= e((string)$b['party_size']) ?></td>
                    <td>
                        <?php if ($status === 'Pending'): ?>
                            <span class="badge text-bg-warning">Pending</span>
                        <?php elseif ($status === 'Approved'): ?>
                            <span class="badge text-bg-success">Approved</span>
                        <?php elseif ($status === 'Cancelled'): ?>
                            <span class="badge text-bg-secondary">Cancelled</span>
                        <?php else: ?>
                            <span class="badge text-bg-primary"><?= e($status) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e(number_format($total, 2)) ?></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <?php if ($canApprove): ?>
                                <form method="post" action="<?= e(base_url('booking/update-status')) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="booking_id" value="<?= e((string)$b['id']) ?>">
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" class="btn btn-outline-success btn-sm">Approve</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canComplete): ?>
                                <form method="post" action="<?= e(base_url('booking/update-status')) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="booking_id" value="<?= e((string)$b['id']) ?>">
                                    <input type="hidden" name="status" value="Completed">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Complete</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canCancel): ?>
                                <form method="post" action="<?= e(base_url('booking/cancel')) ?>"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="booking_id" value="<?= e((string)$b['id']) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">
        No bookings yet. Once visitors start booking activities on your farms, they will appear here.
    </div>
<?php endif; ?>

