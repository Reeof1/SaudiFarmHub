<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-house-heart"></i>
            <span>Owner workspace</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-1 fh-section-title">Your Farms</h1>
        <p class="fh-muted mb-0">Create and manage farms you offer.</p>
    </div>
    <a class="btn btn-success" href="<?= e(base_url('owner/farm/create')) ?>">Add Farm</a>
</div>

<?php if (!empty($farms)): ?>
    <div class="row g-3">
        <?php foreach ($farms as $farm): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card fh-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-success"><?= e($farm['name']) ?></h5>
                        <p class="card-text small fh-muted mb-2">
                            <i class="bi bi-geo-alt"></i> <?= e($farm['city'] ?? 'No city') ?>
                            &middot;
                            <i class="bi bi-eye"></i> <?= (int)($visitCounts[(int)$farm['id']] ?? 0) ?> views
                        </p>
                        <p class="card-text small fh-muted flex-grow-1"><?= e(mb_substr($farm['description'] ?? '', 0, 100)) ?>...</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a class="btn btn-outline-success btn-sm" href="<?= e(base_url('owner/farm/edit?id=' . (int)$farm['id'])) ?>">
                                Edit
                            </a>
                            <a class="btn btn-outline-success btn-sm" href="<?= e(base_url('owner/farm/activities?farm_id=' . (int)$farm['id'])) ?>">
                                Activities
                            </a>
                            <form method="post" action="<?= e(base_url('owner/farm/delete')) ?>" onsubmit="return confirm('Delete this farm?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e((string)$farm['id']) ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e(base_url('owner/farms?page=' . $i)) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php else: ?>
    <div class="fh-card-soft p-4">
        You don't have any farms yet. Create your first one to start listing activities.
    </div>
<?php endif; ?>

