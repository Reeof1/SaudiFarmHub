<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card fh-card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="icon-bubble bg-warning-subtle text-warning">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 text-success fw-bold">Create your account</h1>
                        <div class="small fh-muted">Join as a visitor or a farm owner.</div>
                    </div>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= e(base_url('register')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="visitor">Visitor</option>
                            <option value="owner">Farm Owner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Create Account</button>
                </form>
                <div class="small fh-muted mt-3">
                    Already have an account? <a href="<?= e(base_url('login')) ?>" class="link-success fw-semibold">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

