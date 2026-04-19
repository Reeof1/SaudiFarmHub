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
            <label class="form-label small fh-muted mb-1">Activity type</label>
            <input type="text" id="search-activity-type" class="form-control" placeholder="e.g. Tour, Workshop">
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
    <?php if (!empty($farms)): ?>
        <?php foreach ($farms as $farm): ?>
            <div class="col-md-4">
                <div class="card fh-card h-100">
                    <img src="<?= e($farm['main_image'] ?? base_url('assets/img/farm-placeholder.jpg')) ?>"
                         class="card-img-top" alt="Farm image">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-success"><?= e($farm['name']) ?></h5>
                        <p class="card-text small mb-1">
                            <i class="bi bi-geo-alt"></i> <?= e($farm['city'] ?? 'Unknown city') ?>
                        </p>
                        <?php if (!empty($sortedByDistance) && isset($farm['distance_km']) && $farm['distance_km'] !== null): ?>
                            <p class="card-text small text-success mb-1">
                                <i class="bi bi-signpost-2"></i>
                                <?= e(number_format((float)$farm['distance_km'], 1)) ?> km away
                            </p>
                        <?php endif; ?>
                        <p class="card-text small fh-muted flex-grow-1">
                            <?= e(mb_substr($farm['description'] ?? '', 0, 120)) ?>...
                        </p>
                        <a href="<?= e(base_url('farm/view?farm_id=' . (int)$farm['id'])) ?>" class="btn btn-outline-success btn-sm mt-2">
                            View Details &amp; Book
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-light border mb-0">
                No farms available yet.
            </div>
        </div>
    <?php endif; ?>
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

    // Sort farms by distance on first visit. Once we've asked, remember the choice
    // for this tab so we don't re-prompt on every page load.
    (function () {
        if (window.FARMHUB.sortedByDistance) return;
        if (!navigator.geolocation) return;

        var asked = sessionStorage.getItem('fh_geo_asked');
        if (asked) return;

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                sessionStorage.setItem('fh_geo_asked', '1');
                var url = new URL(window.location.href);
                url.searchParams.set('user_lat', pos.coords.latitude.toFixed(8));
                url.searchParams.set('user_lng', pos.coords.longitude.toFixed(8));
                window.location.replace(url.toString());
            },
            function () {
                sessionStorage.setItem('fh_geo_asked', '1');
            },
            { timeout: 10000 }
        );
    })();
</script>
