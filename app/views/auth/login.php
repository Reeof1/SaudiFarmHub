<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card fh-card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="icon-bubble bg-success-subtle text-success">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 text-success fw-bold">Login</h1>
                        <div class="small fh-muted">Access your dashboard and bookings.</div>
                    </div>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= e(base_url('login')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>
                <div class="small fh-muted mt-3">
                    New here? <a href="<?= e(base_url('register')) ?>" class="link-success fw-semibold">Create an account</a>
                </div>
            </div>
        </div>
    </div>
</div>

