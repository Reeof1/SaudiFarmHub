<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-heart-fill text-danger"></i>
            <span>Your saved farms</span>
        </div>
        <h1 class="h3 fw-bold text-success fh-section-title mb-1">My Favorites</h1>
        <p class="fh-muted mb-0">Farms you've marked as favorite. Click the heart again to remove.</p>
    </div>
    <a class="btn btn-outline-success" href="<?= e(base_url('dashboard/visitor')) ?>">
        <i class="bi bi-arrow-left me-2"></i>Back to dashboard
    </a>
</div>

<div id="farm-list" class="row g-4">
    <?php if (!empty($farms)): ?>
        <?php foreach ($farms as $farm): ?>
            <div class="col-md-4">
                <div class="card fh-card h-100 position-relative">
                    <img src="<?= e(!empty($farm['main_image']) ? base_url($farm['main_image']) : base_url('assets/img/farm-placeholder.jpg')) ?>"
                         class="card-img-top" alt="Farm image"
                         style="height: 200px; object-fit: cover;">
                    <button type="button"
                            class="btn btn-sm btn-danger fh-fav-card-btn position-absolute top-0 end-0 m-2"
                            data-farm-id="<?= (int)$farm['id'] ?>"
                            data-favorited="1"
                            aria-label="Remove from favorites">
                        <i class="bi bi-heart-fill"></i>
                    </button>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-success"><?= e($farm['name']) ?></h5>
                        <p class="card-text small mb-1">
                            <i class="bi bi-geo-alt"></i> <?= e($farm['city'] ?? 'Unknown city') ?>
                        </p>
                        <p class="card-text small fh-muted flex-grow-1">
                            <?= e(mb_substr($farm['description'] ?? '', 0, 120)) ?>...
                        </p>
                        <a href="<?= e(base_url('farm/view?farm_id=' . (int)$farm['id'])) ?>"
                           class="btn btn-outline-success btn-sm mt-2">
                            View Details &amp; Book
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-light border mb-0">
                You haven't favorited any farms yet.
                <a href="<?= e(base_url('farms')) ?>" class="alert-link">Browse farms</a>
                and tap the heart to save them here.
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    (function () {
        const baseUrl = <?= json_encode(base_url()) ?>;
        const csrfToken = <?= json_encode(csrf_token()) ?>;

        document.querySelectorAll('.fh-fav-card-btn').forEach(function (btn) {
            btn.addEventListener('click', async function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                const farmId = btn.dataset.farmId;
                btn.disabled = true;
                try {
                    const res = await fetch(baseUrl + 'favorite/toggle', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({
                            csrf_token: csrfToken,
                            farm_id: farmId,
                        }),
                    });
                    const data = await res.json();
                    if (!data.success) return;

                    // On the favorites page, removing = hide the card
                    if (!data.favorited) {
                        const card = btn.closest('.col-md-4');
                        if (card) card.remove();
                    }
                } catch (e) {
                    // ignore
                } finally {
                    btn.disabled = false;
                }
            });
        });
    })();
</script>
