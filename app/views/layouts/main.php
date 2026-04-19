<?php
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FarmHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm sticky-top">
    <div class="container fh-container">
        <a class="navbar-brand fw-bold" href="<?= e(base_url()) ?>">FarmHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= e(base_url()) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(base_url('farms')) ?>">Farms</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (!empty($_SESSION['user'])): ?>
                    <li class="nav-item me-3">
                        <?php $role = (string)($_SESSION['user']['role'] ?? 'visitor'); ?>
                        <span class="navbar-text text-white d-flex align-items-center gap-2">
                            <span class="d-none d-lg-inline">Hello, <?= e($_SESSION['user']['name']) ?></span>
                            <span class="badge text-bg-light text-success border">
                                <?= e(ucfirst($role)) ?>
                            </span>
                        </span>
                    </li>
                    <li class="nav-item me-2">
                        <?php
                        $dashPath = 'dashboard/visitor';
                        if ($role === 'owner') {
                            $dashPath = 'dashboard/owner';
                        } elseif ($role === 'admin') {
                            $dashPath = 'dashboard/admin';
                        }
                        ?>
                        <a class="btn btn-light btn-sm fw-semibold" href="<?= e(base_url($dashPath)) ?>">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="btn btn-outline-light btn-sm fw-semibold" href="<?= e(base_url('settings')) ?>">
                            <i class="bi bi-gear me-1"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link text-white position-relative" href="<?= e(base_url('notifications')) ?>">
                            <i class="bi bi-bell"></i>
                            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning" style="display:none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('logout')) ?>">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('login')) ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('register')) ?>">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container fh-container my-4">
    <?= $content ?>
</main>

<footer class="container fh-container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-3 fh-footer">
        <div class="small">
            <span class="fw-semibold">FarmHub</span>
            <span class="mx-2">·</span>
            <span>Discover farms. Book experiences. Manage operations.</span>
        </div>
        <div class="small d-flex gap-3">
            <a href="<?= e(base_url()) ?>">Home</a>
            <a href="<?= e(base_url('farms')) ?>">Farms</a>
            <?php if (!empty($_SESSION['user'])): ?>
                <a href="<?= e(base_url('notifications')) ?>">Notifications</a>
            <?php endif; ?>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(base_url('assets/js/app.js')) ?>"></script>
<script>
    // Notification badge loader (best-effort).
    (async function () {
        try {
            const res = await fetch('<?= e(base_url('notifications/unread-count')) ?>');
            const data = await res.json();
            const badge = document.getElementById('notif-badge');
            if (!badge) return;
            const count = Number(data.count || 0);
            if (count > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = String(count);
            }
        } catch (e) {
            // Ignore badge errors.
        }
    })();
</script>
</body>
</html>

