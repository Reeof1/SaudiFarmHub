<?php
/** @var array $schedule */
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-calendar2-week"></i>
            <span>Schedule manager</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-3 fh-section-title">Edit Schedule</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="fh-card-soft p-4">
            <div class="card-body">
                <form method="post" action="<?= e(base_url('owner/farm/schedule/update')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= e((string)$schedule['id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="schedule_date" class="form-control" required
                                   value="<?= e((string)$schedule['schedule_date']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" required
                                   value="<?= e((string)$schedule['start_time']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" required
                                   value="<?= e((string)$schedule['end_time']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity" class="form-control" min="1" max="500" required
                                   value="<?= e((string)$schedule['capacity']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" min="0" step="0.01" required
                                   value="<?= e((string)$schedule['price']) ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="<?= e(base_url('owner/farm/schedules?activity_id=' . (int)$schedule['activity_id'])) ?>"
                           class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

