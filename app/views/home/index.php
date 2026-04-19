<div class="home-hero p-4 p-lg-5 rounded-4 shadow-sm overflow-hidden position-relative">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <div class="fh-chip d-inline-flex align-items-center gap-2 mb-3">
                <i class="bi bi-stars"></i>
                <span>Modern agritourism booking platform</span>
            </div>
            <h1 class="display-6 fw-bold text-white mb-3">
                FarmHub helps people discover farms and book real agricultural experiences.
            </h1>
            <p class="text-white-75 mb-4 lead">
                Browse verified farms, explore activities, pick available dates and time slots, and manage bookings with real-time notifications.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-warning btn-lg" href="<?= e(base_url('farms')) ?>">
                    Explore Farms
                </a>
                <?php if (empty($_SESSION['user'])): ?>
                    <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('register')) ?>">
                        Create Account
                    </a>
                    <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('login')) ?>">
                        Login
                    </a>
                <?php else: ?>
                    <?php $role = $_SESSION['user']['role'] ?? 'visitor'; ?>
                    <?php if ($role === 'owner'): ?>
                        <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('dashboard/owner')) ?>">Owner Dashboard</a>
                    <?php elseif ($role === 'admin'): ?>
                        <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('dashboard/admin')) ?>">Admin Dashboard</a>
                    <?php else: ?>
                        <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('dashboard/visitor')) ?>">Visitor Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="glass-card p-4 rounded-4 border fh-card-soft">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="icon-bubble bg-success-subtle text-success">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Smart booking</div>
                        <div class="text-muted small">Capacity checks, conflict prevention, and status tracking.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="icon-bubble bg-warning-subtle text-warning">
                        <i class="bi bi-bell"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Notifications</div>
                        <div class="text-muted small">Confirmation alerts and real-time unread badge.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-bubble bg-info-subtle text-info">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Secure access</div>
                        <div class="text-muted small">CSRF protection, password hashing, and role-based authorization.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-4">
    <div class="col-md-4">
        <div class="card fh-card h-100">
            <div class="card-body">
                <div class="icon-bubble bg-success-subtle text-success mb-3">
                    <i class="bi bi-search"></i>
                </div>
                <h2 class="h6 fw-bold">Advanced search & filters</h2>
                <p class="text-muted mb-0">
                    Find farms by name, location, and activity type, plus dynamic filtering by availability date and price range.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card fh-card h-100">
            <div class="card-body">
                <div class="icon-bubble bg-warning-subtle text-warning mb-3">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <h2 class="h6 fw-bold">Farm owner operations</h2>
                <p class="text-muted mb-0">
                    Create farms, add activities, publish schedules with pricing and capacity, and manage bookings from a centralized dashboard.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card fh-card h-100">
            <div class="card-body">
                <div class="icon-bubble bg-info-subtle text-info mb-3">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h2 class="h6 fw-bold">Admin oversight</h2>
                <p class="text-muted mb-0">
                    Approve farms, monitor bookings, and track activity across the platform with reporting foundations.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-lg-10">
        <div class="p-4 p-lg-5 fh-card-soft">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h2 class="h4 fw-bold text-success mb-2">Built for three roles</h2>
                    <p class="text-muted mb-0">
                        Visitors discover and book. Owners publish experiences and manage operations. Admins approve and oversee system health.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a class="btn btn-success btn-lg" href="<?= e(base_url('farms')) ?>">Start browsing farms</a>
                </div>
            </div>
        </div>
    </div>
</div>

