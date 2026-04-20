<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-leaf"></i>
            <span>Verified farms · Bookable activities</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">Discover Farms</h1>
        <p class="fh-muted mb-0">Browse agritourism experiences and book available time slots.</p>
    </div>
</div>

<?php if (!empty($sortedByDistance)): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="status">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Showing farms sorted by distance from your location.</span>
    </div>
<?php endif; ?>

<div class="fh-card-soft p-3 p-lg-4 mb-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fh-muted mb-1">Farm name</label>
            <input type="text" id="search-name" class="form-control" placeholder="e.g. Green Valley">
        </div>
        <div class="col-md-4">
            <label class="form-label small fh-muted mb-1">City</label>
            <select id="search-city" class="form-select">
                <option value="">All cities</option>
                <?php foreach (($cities ?? []) as $cityOption): ?>
                    <option value="<?= e($cityOption) ?>"><?= e($cityOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fh-muted mb-1">Activity types</label>
            <div class="dropdown fh-activity-dropdown">
                <button type="button"
                        class="form-select text-start d-flex justify-content-between align-items-center"
                        id="search-activity-toggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false">
                    <span id="search-activity-label" class="fh-muted">All activity types</span>
                </button>
                <div class="dropdown-menu p-2 w-100" style="max-height: 260px; overflow-y: auto;">
                    <?php foreach (\Core\ActivityTypes::LIST as $typeOption): ?>
                        <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox"
                                   class="form-check-input m-0 fh-activity-check"
                                   value="<?= e($typeOption) ?>">
                            <span><?= e($typeOption) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fh-muted mb-1">Availability date</label>
            <input type="date" id="search-availability-date" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small fh-muted mb-1">Min price</label>
            <input type="number" id="search-min-price" class="form-control" min="0" step="0.01" placeholder="0">
        </div>
        <div class="col-md-2">
            <label class="form-label small fh-muted mb-1">Max price</label>
            <input type="number" id="search-max-price" class="form-control" min="0" step="0.01" placeholder="100">
        </div>
        <div class="col-md-4 d-grid">
            <label class="form-label small fh-muted mb-1">&nbsp;</label>
            <button id="search-button" class="btn btn-success">
                <i class="bi bi-search me-2"></i>Search farms
            </button>
        </div>
    </div>
</div>

<div id="farm-list" class="row g-4">
    <?php include __DIR__ . '/_farm_cards.php'; ?>
</div>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
    window.FARMHUB = {
        baseUrl: <?= json_encode(base_url()) ?>,
        searchUrl: <?= json_encode(base_url('search/farms')) ?>,
        csrfToken: <?= json_encode(csrf_token()) ?>,
        sortedByDistance: <?= !empty($sortedByDistance) ? 'true' : 'false' ?>
    };

    // Auto-apply sort by distance whenever the user visits the farm list.
    // Cache the last known location so we don't re-prompt for GPS every time.
    (function () {
        if (window.FARMHUB.sortedByDistance) return;
        if (!navigator.geolocation) return;

        var cachedLat = sessionStorage.getItem('fh_geo_lat');
        var cachedLng = sessionStorage.getItem('fh_geo_lng');

        if (cachedLat && cachedLng) {
            var url = new URL(window.location.href);
            url.searchParams.set('user_lat', cachedLat);
            url.searchParams.set('user_lng', cachedLng);
            window.location.replace(url.toString());
            return;
        }

        if (sessionStorage.getItem('fh_geo_denied')) return;

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                var lat = pos.coords.latitude.toFixed(8);
                var lng = pos.coords.longitude.toFixed(8);
                sessionStorage.setItem('fh_geo_lat', lat);
                sessionStorage.setItem('fh_geo_lng', lng);
                var url = new URL(window.location.href);
                url.searchParams.set('user_lat', lat);
                url.searchParams.set('user_lng', lng);
                window.location.replace(url.toString());
            },
            function () {
                sessionStorage.setItem('fh_geo_denied', '1');
            },
            { timeout: 10000 }
        );
    })();
</script>
