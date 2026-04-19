<?php
/** @var array $activity */
/** @var array $schedules */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-calendar3"></i>
            <span>Schedule manager</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-1 fh-section-title">Schedules – <?= e((string)$activity['name']) ?></h1>
        <p class="fh-muted mb-0">Define available dates, times, capacity, and pricing.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary"
           href="<?= e(base_url('owner/farm/activities?farm_id=' . (int)$activity['farm_id'])) ?>">
            Back to Activities
        </a>
        <a class="btn btn-success"
           href="<?= e(base_url('owner/farm/schedule/create?activity_id=' . (int)$activity['id'])) ?>">
            Add Schedule
        </a>
    </div>
</div>

<?php if (!empty($schedules)): ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Capacity</th>
                <th>Price</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($schedules as $s): ?>
                <tr>
                    <td><?= e((string)$s['schedule_date']) ?></td>
                    <td><?= e((string)$s['start_time']) ?> – <?= e((string)$s['end_time']) ?></td>
                    <td><?= e((string)$s['capacity']) ?></td>
                    <td><?= e(number_format((float)$s['price'], 2)) ?></td>
                    <td class="text-end">
                        <a class="btn btn-outline-primary btn-sm"
                           href="<?= e(base_url('owner/farm/schedule/edit?id=' . (int)$s['id'])) ?>">
                            Edit
                        </a>
                        <form method="post" action="<?= e(base_url('owner/farm/schedule/delete')) ?>"
                              class="d-inline"
                              onsubmit="return confirm('Delete this schedule? Existing bookings will remain.');">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= e((string)$s['id']) ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="fh-card-soft p-4">
        No schedules yet. Click <strong>Add Schedule</strong> to configure the first time slot for this activity.
    </div>
<?php endif; ?>

