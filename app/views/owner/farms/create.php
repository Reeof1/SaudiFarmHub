<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-plus-square"></i>
            <span>Farm setup</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-3 fh-section-title">Add Farm</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="fh-card-soft p-4">
            <div class="card-body">
                <form method="post" action="<?= e(base_url('owner/farm/store')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="mb-3">
                        <label class="form-label">Farm Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('owner/farms')) ?>">Cancel</a>
                        <button type="submit" class="btn btn-success flex-grow-1">Create Farm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

