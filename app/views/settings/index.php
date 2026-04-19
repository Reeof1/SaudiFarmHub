<?php
/** @var array $user */
/** @var string|null $error */
/** @var string|null $success */
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-gear"></i>
            <span>Account settings</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">Settings</h1>
        <p class="fh-muted mb-0">Update your profile information and password.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold mb-3">Personal information</h2>
            <form method="post" action="<?= e(base_url('settings')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="mb-3">
                    <label class="form-label">Full name</label>
                    <input type="text" name="name" class="form-control" required value="<?= e((string)($user['name'] ?? '')) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required value="<?= e((string)($user['email'] ?? '')) ?>">
                </div>

                <hr class="my-4">

                <h2 class="h6 fw-bold mb-2">Change password (optional)</h2>
                <p class="fh-muted small mb-3">Leave blank if you don’t want to change your password.</p>

                <div class="mb-3">
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">New password</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" autocomplete="new-password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm new password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save2 me-2"></i>Save changes
                    </button>
                    <a class="btn btn-outline-secondary" href="<?= e(base_url()) ?>">Back</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold mb-2">Account details</h2>
            <div class="fh-muted small mb-3">These details are for your reference.</div>

            <div class="d-flex justify-content-between border-bottom py-2">
                <div class="fh-muted">Role</div>
                <div class="fw-semibold"><?= e(ucfirst((string)($user['role'] ?? ($_SESSION['user']['role'] ?? 'visitor')))) ?></div>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2">
                <div class="fh-muted">User ID</div>
                <div class="fw-semibold"><?= e((string)($user['id'] ?? ($_SESSION['user']['id'] ?? ''))) ?></div>
            </div>
            <div class="d-flex justify-content-between py-2">
                <div class="fh-muted">Member since</div>
                <div class="fw-semibold"><?= e((string)($user['created_at'] ?? '')) ?></div>
            </div>
        </div>
    </div>
</div>

