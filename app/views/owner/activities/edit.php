<?php
/** @var array $activity */
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-pencil-square"></i>
            <span>Activity manager</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-3 fh-section-title">Edit Activity</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="fh-card-soft p-4">
            <div class="card-body">
                <form method="post" action="<?= e(base_url('owner/farm/activity/update')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= e((string)$activity['id']) ?>">

                    <div class="mb-3">
                        <label class="form-label">Activity Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= e((string)$activity['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Activity Type</label>
                        <input type="text" name="activity_type" class="form-control" required
                               value="<?= e((string)$activity['activity_type']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?= e((string)$activity['description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= e(base_url('owner/farm/activities?farm_id=' . (int)$activity['farm_id'])) ?>"
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

