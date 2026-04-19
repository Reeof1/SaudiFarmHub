<?php
/** @var array $bookings */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-calendar2-check"></i>
            <span>Visitor bookings</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-1 fh-section-title">My Bookings</h1>
        <p class="fh-muted mb-0">View and manage your farm activity bookings.</p>
    </div>
    <a href="<?= e(base_url('farms')) ?>" class="btn btn-outline-success">Browse Farms</a>
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
                <th>Price</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <?php
                $status = (string)$b['status_name'];
                $canCancel = in_array($status, ['Pending', 'Approved'], true);
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
                    <td><?= e(number_format((float)$b['price'] * (int)$b['party_size'], 2)) ?></td>
                    <td class="text-end">
                        <?php if ($canCancel): ?>
                            <form method="post" action="<?= e(base_url('booking/cancel')) ?>"
                                  onsubmit="return confirm('Cancel this booking?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="booking_id" value="<?= e((string)$b['id']) ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">
        You have no bookings yet. Browse farms and book your first activity.
    </div>
<?php endif; ?>

